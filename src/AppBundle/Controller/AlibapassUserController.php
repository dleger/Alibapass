<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use AppBundle\Entity\AlibapassUser;
use Symfony\Component\HttpFoundation\Session\Session;


class AlibapassUserController extends Controller
{
    /**
     * @Route("/users/"), name="users", options={"expose"=true})
     */
    public function alibapassUserAction(Request $request) {

        $session = new Session();

        $user_session_id = $session->get('user_id');
        $user_session_admin = $session->get('admin');
        $user_session_fullname = $session->get('fullname');

        if ($user_session_id > 0 && $user_session_admin) {
            // ****************************
            // * Treatment Add a new User *
            // ****************************
            if ($request->isXmlHttpRequest()) {
                $user_action = $request->request->get('user_action');

                if ($user_action == 'Create') {
                    $new_user_firstname = $request->request->get('new_user_firstname');
                    $new_user_lastname = $request->request->get('new_user_lastname');
                    $new_user_email = $request->request->get('new_user_email');
                    $new_user_username = $request->request->get('new_user_username');
                    $new_user_groups = $request->request->get('new_user_groups');
                    $new_user_admin = $request->request->get('new_user_admin');
                    $new_user_active = $request->request->get('new_user_active');

                    $response = new JsonResponse();

                    // ***************
                    // *  Validation *
                    // ***************
                    $data = self::user_validation($this, $new_user_firstname, $new_user_lastname, $new_user_email, $new_user_username);

                    $existing_user_email = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('email' => $new_user_email));
                    $existing_username = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('username' => $new_user_username));

                    // *******************************
                    // * Record new User in Database *
                    // *******************************
                    if ($data['success'] && empty($existing_user_email) && empty($existing_username)) {
                        $new_user = new AlibapassUser();
                        $new_user->setFirstname($new_user_firstname);
                        $new_user->setLastname($new_user_lastname);
                        $new_user->setEmail($new_user_email);
                        $new_user->setUsername($new_user_username);
                        $new_user->setAdmin($new_user_admin);
                        $new_user->setActive($new_user_active);

                        if (is_array($new_user_groups)) {
                            foreach ($new_user_groups as $new_user_group) {
                                $current_group = $this->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->find($new_user_group);

                                $new_user->addAlibapassgroup($current_group);
                            }
                        }

                        $generator = new ComputerPasswordGenerator();

                        $generator
                            ->setUppercase()
                            ->setLowercase()
                            ->setNumbers()
                            ->setSymbols(TRUE)
                            ->setLength(8);

                        $password = $generator->generatePassword();

                        $new_user->setPassword(password_hash($password, PASSWORD_DEFAULT, array('cost' => 15)));

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($new_user);
                        $em->flush();

                        // ****************************
                        // * Send Email with password *
                        // ****************************
                        $email_message = \Swift_Message::newInstance()
                            ->setSubject('Your new Account has been created on Alibapass')
                            ->setFrom('noreply@wave-group.co.uk')
                            ->setTo($new_user_email)
                            ->setBody('Your username: ' . $new_user_username . ' - Your Password: ' . $password, 'text/html' . "<br /><br />You can login to the password manager here: http://alibapass.wave-group.co.uk");

                        $this->get('mailer')->send($email_message);

                    } else {
                        if (!empty($existing_user_email)) {
                            $data[count($data)]['email'] = 'This Email has been already registered';
                        }

                        if (!empty($existing_username)) {
                            $data[count($data)]['username'] = 'This Username has been already registered';
                        }

                        $data['success'] = FALSE;
                    }

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($user_action == 'Delete') {

                    // *************************
                    // * Delete selected users *
                    // *************************
                    $em = $this->getDoctrine()->getManager();

                    $selected_users = $request->request->get('selected_users');

                    for ($cpt_users = 0; $cpt_users < count($selected_users); $cpt_users++) {
                        $current_user = $em->getRepository('AppBundle:AlibapassUser')->find($selected_users[$cpt_users]);

                        $em->remove($current_user);
                        $em->flush();
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => 'success'
                    ));

                    return $response;
                } else if ($user_action == 'Edit') {

                    // **********************
                    // * Edit selected User *
                    // **********************
                    $em = $this->getDoctrine()->getManager();

                    $edit_user_id = $request->request->get('edit_user_id');
                    $edit_user_firstname = $request->request->get('edit_user_firstname');
                    $edit_user_lastname = $request->request->get('edit_user_lastname');
                    $edit_user_email = $request->request->get('edit_user_email');
                    $edit_user_username = $request->request->get('edit_user_username');
                    $edit_user_groups = $request->request->get('edit_user_groups');
                    $edit_user_admin = $request->request->get('edit_user_admin');
                    $edit_user_active = $request->request->get('edit_user_active');

                    // **************
                    // * Validation *
                    // **************
                    $data = self::user_validation($this, $edit_user_firstname, $edit_user_lastname, $edit_user_email, $edit_user_username);

                    $existing_user_email_from_current_id = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $edit_user_id))->getEmail();
                    $existing_user_email_obj = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('email' => $edit_user_email));

                    if (count($existing_user_email_obj) > 0) {
                        $existing_user_email = $existing_user_email_obj->getEmail();
                    } else {
                        $existing_user_email = '';
                    }

                    $existing_user_username_from_current_id = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $edit_user_id))->getUsername();
                    $existing_user_username_obj = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('username' => $edit_user_username));

                    if (count($existing_user_username_obj) > 0) {
                        $existing_user_username = $existing_user_username_obj->getUsername();
                    } else {
                        $existing_user_username = '';
                    }


                    if ($data['success'] && (empty($existing_user_email) || $existing_user_email == $existing_user_email_from_current_id) && (empty($existing_user_username) || $existing_user_username == $existing_user_username_from_current_id)) {

                        $current_user = $em->getRepository('AppBundle:AlibapassUser')->find($edit_user_id);
                        $current_user->setFirstname($edit_user_firstname);
                        $current_user->setLastname($edit_user_lastname);
                        $current_user->setEmail($edit_user_email);
                        $current_user->setUsername($edit_user_username);
                        $current_user->setAdmin($edit_user_admin);
                        $current_user->setActive($edit_user_active);

                        // **********************************
                        // * First delete associated Groups *
                        // **********************************
                        $current_user_groups = $current_user->getAlibapassgroup();

                        foreach ($current_user_groups as $current_user_group) {
                            $current_user->removeAlibapassgroup($current_user_group);
                        }

                        // ******************************
                        // * Then add associated groups *
                        // ******************************
                        if (is_array($edit_user_groups)) {
                            foreach ($edit_user_groups as $edit_user_group) {
                                $current_group = $this->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->find($edit_user_group);

                                $current_user->addAlibapassgroup($current_group);
                            }
                        }

                        // ****************************************
                        // * Rename Fullname in Entries for cases *
                        // * where entries are only available to  *
                        // * a particular user instead of a Group *
                        // ****************************************
                        $owned_entries = $this->getDoctrine()->getRepository('AppBundle:Entry')->findBy(array('user' => $edit_user_id), array('id' => 'ASC'));

                        for ($cpt_entries = 0; $cpt_entries < count($owned_entries); $cpt_entries++) {
                            $current_entry = $em->getRepository('AppBundle:Entry')->find($owned_entries[$cpt_entries]->getId());

                            $current_entry->setUserfullname($edit_user_firstname . ' ' . $edit_user_lastname);
                        }

                        $em->flush();

                    } else {

                        if (!empty($existing_user_email)) {
                            $data[count($data)]['email'] = 'This Email has been already registered';
                        }

                        if (!empty($existing_user_username)) {
                            $data[count($data)]['username'] = 'This Username has been already registered';
                        }

                        $data['success'] = FALSE;
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($user_action == 'Reset') {
                    $em = $this->getDoctrine()->getManager();

                    $current_user_id = $request->request->get('current_user_id');

                    // ***************************
                    // * Generate a new password *
                    // ***************************
                    $generator = new ComputerPasswordGenerator();

                    $generator
                        ->setUppercase()
                        ->setLowercase()
                        ->setNumbers()
                        ->setSymbols(TRUE)
                        ->setLength(8);

                    $password = $generator->generatePassword();

                    $current_user = $em->getRepository('AppBundle:AlibapassUser')->find($current_user_id);
                    $current_user->setPassword(password_hash($password, PASSWORD_DEFAULT, array('cost' => 15)));

                    $em->flush();

                    // ****************************
                    // * Send Email with password *
                    // ****************************
                    $current_user_username = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $current_user_id))->getUsername();
		    $current_user_email = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $current_user_id))->getEmail();

                    $email_message = \Swift_Message::newInstance()
                        ->setSubject('Your password has been reseted')
                        ->setFrom('noreply@wave-group.co.uk')
                        ->setTo($current_user_email)
                        ->setBody('Your username: ' . $current_user_username . ' - Your NEW Passsowrd: ' . $password, 'text/html');

                    $this->get('mailer')->send($email_message);


                    $data['success'] = TRUE;

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                }

            } else {
                // *************************
                // * Users page generation *
                // *************************
                return self::render_users($this, $user_session_id, $user_session_admin, $user_session_fullname);
            }
        } else {
            if ($user_session_id > 0 && !$user_session_admin) {
                return $this->redirectToRoute('entries');
            } else {
                return $this->redirectToRoute('login');
            }
        }
    }
    
    protected static function render_users($users_content, $user_session_id, $user_session_admin, $user_session_fullname) {
        $all_groups = $users_content->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->findBy(array(), array('name' => 'ASC'));
        
        $all_users = $users_content->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findBy(array(), array('lastname' => 'ASC'));
        
        return $users_content->render('users.html.twig', array(
            'current_category' => 'Users',
            'all_groups' => $all_groups,
            'all_users' => $all_users,
            'user_session_id' => $user_session_id,
            'user_session_admin' => $user_session_admin,
            'user_session_fullname' => $user_session_fullname
        ));
    }
    
    // ***********************
    // * New User Validation *
    // ***********************
    protected static function user_validation($users_content, $new_user_firstname, $new_user_lastname, $new_user_email, $new_user_username) {
        $new_user = new AlibapassUser();
        $new_user->setFirstname($new_user_firstname);
        $new_user->setLastname($new_user_lastname);
        $new_user->setEmail($new_user_email);
        $new_user->setUsername($new_user_username);
        
        $validator = $users_content->get('validator');
        $errors = $validator->validate($new_user);
        
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
