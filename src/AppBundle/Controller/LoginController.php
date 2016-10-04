<?php
namespace AppBundle\Controller;

use acurrieclark\PhpPasswordVerifier\Verifier;
use AppBundle\Entity\AlibapassUser;
use AppBundle\Entity\Firewall;
use AppBundle\Entity\LostCredential;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginController extends Controller
{
    public static $IPS_WHITELIST = array('92.207.218.180', '52.17.219.44');

    /**
     * @Route("/login/"), name="login", options={"expose"=true})
     * @Route(
     *  "/login/{param}/",
     *  ), name="login"
     */
    public function loginAction(Request $request, $param = NULL) {

	error_log('test again');
        // *******************
        // * Treatment Login *
        //********************
        if (!is_null($param)) {
            // ******************************
            // * Checking if token is valid *
            // ******************************
            $token_obj = $this->getDoctrine()->getRepository('AppBundle:LostCredential')->findOneBy(array('token' => $param));

            if (count($token_obj) > 0) {
                if (time() - $token_obj->getDatetimets() <= 3600) {
                    // *************************
                    // * Password Reset Window *
                    // *************************
                    return $this->render('reset-password.html.twig', array(
                        'token' => $param,
                        'current_category' => 'Reset'
                    ));
                } else {
                    $em = $this->getDoctrine()->getManager();

                    $em->remove($token_obj);
                    $em->flush();

                    throw new NotFoundHttpException();
                }
            } else {
                throw new NotFoundHttpException();

                $current_ip = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();

                $em = $this->getDoctrine()->getManager();
                $new_firewall = new Firewall();
                $new_firewall->setIp($current_ip);
                $new_firewall->setType(3);
                $new_firewall->setDatetimets(time());
                $em->persist($new_firewall);
                $em->flush();
            }
        } else {
            if ($request->isXmlHttpRequest()) {

                $login_action = $request->request->get('login_action');

                // *********
                // * Login *
                // *********
                if ($login_action == 'login') {

                    $current_ip = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();

                    $ip_obj_logins = $this->getDoctrine()->getRepository('AppBundle:Firewall')->findBy(array('ip' => $current_ip, 'type' => 1), array('datetimets' => 'DESC'));
                    $ip_ban = FALSE;

                    if (count($ip_obj_logins) >= 10) {
                        if (time() - $ip_obj_logins[0]->getDatetimets() > 7200) {
                            $em = $this->getDoctrine()->getManager();

                            foreach ($ip_obj_logins as $ip_obj_login) {
                                $em->remove($ip_obj_login);
                            }

                            $em->flush();
                        } else {
                            $ip_ban = TRUE;
                        }
                    }

                    $data = '';
                    $username = $request->request->get('username');
                    $password = $request->request->get('password');

                    $record = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('username' => $username));

                    if (count($record) > 0 && !$ip_ban) {
                        if (password_verify($password, $record->getPassword())) {
                            if ($record->getActive()) {
                                $data = 'success';

                                $session = new Session();
                                $session->set('user_id', $record->getId());
                                $session->set('admin', $record->getAdmin());
                                $session->set('fullname', $record->getFirstname() . ' ' . $record->getLastname());
                            } else {
                                $data = 'disabled';
                            }
                        } else {
                            $data = 'wrong';

                            if (!in_array($current_ip, $this::$IPS_WHITELIST)) {
                                $em = $this->getDoctrine()->getManager();
                                $new_firewall = new Firewall();
                                $new_firewall->setIp($current_ip);
                                $new_firewall->setType(1);
                                $new_firewall->setDatetimets(time());
                                $em->persist($new_firewall);
                                $em->flush();
                            }
                        }
                    } else if (!empty($ip_ban)) {
                        $data = 'ban';
                    } else {
                        $data = 'wrong';

                        if (!in_array($current_ip, $this::$IPS_WHITELIST)) {
                            $em = $this->getDoctrine()->getManager();
                            $new_firewall = new Firewall();
                            $new_firewall->setIp($current_ip);
                            $new_firewall->setType(1);
                            $new_firewall->setDatetimets(time());
                            $em->persist($new_firewall);
                            $em->flush();
                        }
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($login_action == 'reset') {
                    $em = $this->getDoctrine()->getManager();

                    $email = $request->request->get('email');
                    $data = array();

                    $validator = Validation::createValidator();
                    $email_violations = $validator->validate($email, array(new Email(), new NotBlank()));

                    $record = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('email' => $email));

                    $current_ip = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();

                    $ip_obj_resets = $this->getDoctrine()->getRepository('AppBundle:Firewall')->findBy(array('ip' => $current_ip, 'type' => 2), array('datetimets' => 'DESC'));
                    $ip_ban = FALSE;

                    if (count($ip_obj_resets) >= 10) {
                        if (time() - $ip_obj_resets[0]->getDatetimets() > 7200) {
                            $em = $this->getDoctrine()->getManager();

                            foreach ($ip_obj_resets as $ip_obj_reset) {
                                $em->remove($ip_obj_reset);
                            }

                            $em->flush();
                        } else {
                            $ip_ban = TRUE;
                        }
                    }

                    if (count($record) > 0 && !$ip_ban) {
                        $data['success'] = 'success';

                        // ******************
                        // * Token Creation *
                        // ******************
                        $token = str_replace(array('+', '/'), array('-', '_'), base64_encode(random_bytes(30)));

                        $lost_credentials = new LostCredential();

                        $user_obj = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->find($record->getId());
                        $lost_credentials->setUser($user_obj);

                        $lost_credentials->setToken($token);
                        $lost_credentials->setDatetimets(time());

                        $em->persist($lost_credentials);
                        $em->flush();

                        $email_message = \Swift_Message::newInstance()
                            ->setSubject('Alibapass Password Reset')
                            ->setFrom('no-reply@wave-group.co.uk')
                            ->setTo($user_obj->getEmail())
                            ->setBody('Please follow this link to reset your password: http://alibapass.wave-group.co.uk/login/' . $token . ' (This link is valid only 1 hour)');

                        $this->get('mailer')->send($email_message);

                        // **************************
                        // * Sending Recovery Email *
                        // **************************
                    } else if ($ip_ban) {
                        $data['success'] = 'Your IP has been banned for the next 2 hours.';
                    } else {
                        if (count($email_violations) > 0) {
                            $data['success'] = $email_violations[0]->getMessage();
                        } else {
                            $data['success'] = 'This email is not registered.';

                            $current_ip = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();

                            $em = $this->getDoctrine()->getManager();
                            $new_firewall = new Firewall();
                            $new_firewall->setIp($current_ip);
                            $new_firewall->setType(2);
                            $new_firewall->setDatetimets(time());
                            $em->persist($new_firewall);
                            $em->flush();
                        }

                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($login_action == 'change') {
                    // *******************
                    // * Password change *
                    // *******************
                    $token = $request->request->get('token');

                    $password_one = $request->request->get('password_one');
                    $password_second = $request->request->get('password_second');

                    $validator = Validation::createValidator();
                    $password_violations = $validator->validate($password_one, array(new NotBlank()));


                    $password_verifier = new Verifier();

                    $password_verifier->setMinLength(8);
                    $password_verifier->setCheckContainsLetters(TRUE);
                    $password_verifier->setCheckContainsNumbers(TRUE);
                    $password_verifier->setCheckContainsCapitals(TRUE);
                    $password_verifier->setCheckContainsSpecialChrs(TRUE);

                    $password_check = $password_verifier->checkPassword($password_one);
                    $password_errors = implode('. ', $password_verifier->getErrors());

                    if (count($password_violations) > 0) {
                        $data['success'] = $password_violations[0]->getMessage();
                    } else if ($password_one != $password_second) {
                        $data['success'] = 'Passwords do not match!';
                    } else if ($password_check) {
                        $data['success'] = 'success';

                        // *******************
                        // * Change Password *
                        // *******************
                        $em = $this->getDoctrine()->getManager();

                        $token_obj = $this->getDoctrine()->getRepository('AppBundle:LostCredential')->findOneBy(array('token' => $token));
                        $current_user = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $token_obj->getUser()->getId()));

                        $current_user->setPassword(password_hash($password_one, PASSWORD_DEFAULT, array('cost' => 15)));

                        $em->flush();

                        $em->remove($token_obj);
                        $em->flush();
                    } else {
                        $data['success'] = $password_errors;
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                }

            } else {
                // *************************
                // * Login page generation *
                // *************************
                return self::render_login($this);
            }
        }
    }

    // *************************
    // * Login page generation *
    // *************************
    protected static function render_login($login_content) {

        return $login_content->render('login.html.twig', array(
            'current_category' => 'Login'
        ));
    }
}
