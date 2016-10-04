<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\AlibapassGroup;
use Symfony\Component\HttpFoundation\Session\Session;

class AlibapassGroupController extends Controller
{
    /**
     * @Route("/groups/"), name="groups", options={"expose"=true})
     */
    public function alibapassGroupAction(Request $request)
    {

        $session = new Session();

        $user_session_id = $session->get('user_id');
        $user_session_admin = $session->get('admin');
        $user_session_fullname = $session->get('fullname');

        if ($user_session_id > 0 && $user_session_admin) {
            // *****************************
            // * Treatment Add a new Group *
            // *****************************
            if ($request->isXmlHttpRequest()) {

                $group_action = $request->request->get('group_action');

                if ($group_action == 'Create') {
                    $new_group_name = $request->request->get('new_group_name');
                    $new_group_description = $request->request->get('new_group_description');

                    $response = new JsonResponse();

                    // ***************
                    // *  Validation *
                    // ***************
                    $data = self::group_validation($this, $new_group_name, $new_group_description);

                    $existing_group_name = $this->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->findOneBy(array('name' => $new_group_name));

                    // ********************************
                    // * Record new Group in Database *
                    // ********************************
                    if ($data['success'] && empty($existing_group_name)) {
                        $new_group = new AlibapassGroup();
                        $new_group->setName($new_group_name);
                        $new_group->setDescription($new_group_description);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($new_group);
                        $em->flush();
                    } else if (!empty($existing_group_name)) {
                        $data[count($data)]['name'] = 'This Name has been already registered';
                        $data['success'] = FALSE;
                    }

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($group_action == 'Delete') {

                    // **************************
                    // * Delete selected groups *
                    // **************************
                    $em = $this->getDoctrine()->getManager();

                    $selected_groups = $request->request->get('selected_groups');

                    for ($cpt_groups = 0; $cpt_groups < count($selected_groups); $cpt_groups++) {
                        $current_group = $em->getRepository('AppBundle:AlibapassGroup')->find($selected_groups[$cpt_groups]);

                        $em->remove($current_group);
                        $em->flush();
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => 'success'
                    ));

                    return $response;
                } else if ($group_action == 'Edit') {

                    // ***********************
                    // * Edit selected Group *
                    // ***********************
                    $em = $this->getDoctrine()->getManager();

                    $edit_group_name = $request->request->get('edit_group_name');
                    $edit_group_description = $request->request->get('edit_group_description');
                    $edit_group_id = $request->request->get('edit_group_id');

                    // **************
                    // * Validation *
                    // **************
                    $data = self::group_validation($this, $edit_group_name, $edit_group_description);

                    $existing_group_name_from_current_id = $this->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->findOneBy(array('id' => $edit_group_id))->getName();
                    $existing_group_name = $this->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->findOneBy(array('name' => $edit_group_name));

                    if ($data['success'] && (count($existing_group_name) == 0 || $edit_group_name == $existing_group_name_from_current_id)) {
                        $current_group = $em->getRepository('AppBundle:AlibapassGroup')->find($edit_group_id);
                        $current_group->setName($edit_group_name);
                        $current_group->setDescription($edit_group_description);
                        $em->flush();
                    } else if (count($existing_group_name) > 0 && $edit_group_name != $existing_group_name_from_current_id) {
                        $data[count($data)]['name'] = 'This Name has been already registered';
                        $data['success'] = FALSE;
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                }

            } else {
                // **************************
                // * Groups page generation *
                // **************************
                return self::render_groups($this, $user_session_id, $user_session_admin, $user_session_fullname);
            }
        } else {
            if ($user_session_id > 0 && !$user_session_admin) {
                return $this->redirectToRoute('entries');
            } else {
                return $this->redirectToRoute('login');
            }
        }
    }

    protected static function render_groups($groups_content, $user_session_id, $user_session_admin, $user_session_fullname)  {
        $all_groups = $groups_content->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->findBy(array(), array('name' => 'ASC'));

        return $groups_content->render('groups.html.twig', array(
            'current_category' => 'Groups',
            'all_groups' => $all_groups,
            'user_session_id' => $user_session_id,
            'user_session_admin' => $user_session_admin,
            'user_session_fullname' => $user_session_fullname
        ));
    }

    // ************************
    // * New Group Validation *
    // ************************
    protected static function group_validation($groups_content, $new_group_name, $new_group_description) {
        $new_group = new AlibapassGroup();
        $new_group->setName($new_group_name);
        $new_group->setDescription($new_group_description);

        $validator = $groups_content->get('validator');
        $errors = $validator->validate($new_group);

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

