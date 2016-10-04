<?php
namespace AppBundle\Controller;

use acurrieclark\PhpPasswordVerifier\Verifier;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use AppBundle\Entity\AlibapassUser;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\NotBlank;


class SettingsController extends Controller
{
    /**
     * @Route("/settings/"), name="settings", options={"expose"=true})
     */
    public function settingsAction(Request $request)
    {
        $session = new Session();

        $user_session_id = $session->get('user_id');
        $user_session_admin = $session->get('admin');
        $user_session_fullname = $session->get('fullname');

        if ($user_session_id > 0) {
            if ($request->isXmlHttpRequest()) {
                $entry_action = $request->request->get('entry_action');

                if ($entry_action == 'Details') {
                    $firstname = $request->request->get('firstname');
                    $lastname = $request->request->get('lastname');
                    $email = $request->request->get('email');
                    $username = $request->request->get('username');

                    $data = self::user_validation($this, $firstname, $lastname, $email, $username);

                    $existing_user_email_from_current_id = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $user_session_id))->getEmail();
                    $existing_user_email_obj = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('email' => $email));

                    if (count($existing_user_email_obj) > 0) {
                        $existing_user_email = $existing_user_email_obj->getEmail();
                    } else {
                        $existing_user_email = '';
                    }

                    $existing_user_username_from_current_id = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $user_session_id))->getUsername();
                    $existing_user_username_obj = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('username' => $username));

                    if (count($existing_user_username_obj) > 0) {
                        $existing_user_username = $existing_user_username_obj->getUsername();
                    } else {
                        $existing_user_username = '';
                    }

                    if ($data['success'] && (empty($existing_user_email) || $existing_user_email == $existing_user_email_from_current_id) && (empty($existing_user_username) || $existing_user_username == $existing_user_username_from_current_id)) {
                        $em = $this->getDoctrine()->getManager();

                        $current_user = $em->getRepository('AppBundle:AlibapassUser')->find($user_session_id);
                        $current_user->setFirstname($firstname);
                        $current_user->setLastname($lastname);
                        $current_user->setEmail($email);
                        $current_user->setUsername($username);

                        $em->flush();

                        $data['success'] = TRUE;
                    } else {
                        if (!empty($existing_user_email) && $existing_user_email != $existing_user_email_from_current_id) {
                            $data[count($data)]['email'] = 'This Email has been already registered';
                        }

                        if (!empty($existing_user_username) && $existing_user_username != $existing_user_username_from_current_id) {
                            $data[count($data)]['username'] = 'This Username has been already registered';
                        }

                        $data['success'] = FALSE;
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($entry_action == 'Password') {
                    $current_password = $request->request->get('current_password');
                    $new_password = $request->request->get('new_password');
                    $password_confirm = $request->request->get('password_confirm');

                    $validator = Validation::createValidator();
                    $password_violations = $validator->validate($new_password, array(new NotBlank()));

                    $password_verifier = new Verifier();
                    $password_verifier->setMinLength(8);
                    $password_verifier->setCheckContainsLetters(TRUE);
                    $password_verifier->setCheckContainsNumbers(TRUE);
                    $password_verifier->setCheckContainsCapitals(TRUE);
                    $password_verifier->setCheckContainsSpecialChrs(TRUE);

                    $password_errors = '';
                    $password_check = $password_verifier->checkPassword($new_password);
                    $password_errors = implode('. ', $password_verifier->getErrors());

                    $cpt_errors = 0;

                    $record = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $user_session_id));
                    if (!password_verify($current_password, $record->getPassword())) {
                        $data[0]['current_password'] = 'Password is incorrect.';
                        $cpt_errors++;
                    }

                    if (count($password_violations) > 0) {
                        $data[0]['new_password'] = $password_violations[0]->getMessage();
                        $cpt_errors++;
                    } else if (!empty($password_errors)) {
                        $data[0]['new_password'] = $password_errors;
                        $cpt_errors++;
                    }

                    if ($new_password != $password_confirm) {
                        $data[0]['password_confirm'] = 'Passwords do not match!';
                        $cpt_errors++;
                    }

                    if ($cpt_errors == 0) {
                        $data['success'] = TRUE;

                        // *******************
                        // * Change Password *
                        // *******************
                        $em = $this->getDoctrine()->getManager();

                        $current_user = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $user_session_id));

                        $current_user->setPassword(password_hash($new_password, PASSWORD_DEFAULT, array('cost' => 15)));

                        $em->flush();
                    } else {
                        $data['success'] = FALSE;
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                }
            } else {
                return self::render_settings($this, $user_session_id, $user_session_admin, $user_session_fullname);
            }
        } else {
            if ($user_session_id > 0 && !$user_session_admin) {
                return $this->redirectToRoute('entries');
            } else {
                return $this->redirectToRoute('login');
            }
        }
    }

    protected static function render_settings($settings_content, $user_session_id, $user_session_admin, $user_session_fullname) {

        $user_obj = $settings_content->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $user_session_id));


        return $settings_content->render('settings.html.twig', array(
            'current_category' => 'Settings',
            'user_session_id' => $user_session_id,
            'user_session_admin' => $user_session_admin,
            'user_session_fullname' => $user_session_fullname,
            'user_first_name' => $user_obj->getFirstname(),
            'user_last_name' => $user_obj->getLastname(),
            'user_email' => $user_obj->getEmail(),
            'user_username' => $user_obj->getUsername()
        ));
    }

    // ********************************
    // * Edit User Details Validation *
    // ********************************
    protected static function user_validation($users_content, $firstname, $lastname, $email, $username) {
        $user = new AlibapassUser();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setUsername($username);

        $validator = $users_content->get('validator');
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $array_errors = array();

            for ($cpt_errors = 0; $cpt_errors < count($errors); $cpt_errors++) {
                $array_errors[$cpt_errors] = array($errors[$cpt_errors]->getPropertyPath() => $errors[$cpt_errors]->getMessage());
            }

            $array_errors['success'] = FALSE;
        } else {
            $array_errors['success'] = TRUE;
        }

        return $array_errors;
    }
}
