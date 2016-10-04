<?php
namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\FieldType;
use AppBundle\Entity\EntryType;
use AppBundle\Entity\Field;
use Symfony\Component\HttpFoundation\Session\Session;

class TypesController extends Controller
{
    /**
     * @Route("/types/"), name="types", options={"expose"=true})
     */
    public function typesAction(Request $request) {

        $session = new Session();

        $user_session_id = $session->get('user_id');
        $user_session_admin = $session->get('admin');
        $user_session_fullname = $session->get('fullname');

        if ($user_session_id > 0 && $user_session_admin) {
            // ****************************
            // * Treatment Add a new Type *
            // ****************************
            if ($request->isXmlHttpRequest()) {

                $type_action = $request->request->get('type_action');

                if ($type_action == 'Add') {

                    $new_type_fields_array = json_decode($request->request->get('new_type_json'));
                    $new_type_name = $request->request->get('new_type_name');

                    // *************************
                    // * Insert new Entry Type *
                    // *************************
                    $em = $this->getDoctrine()->getManager();

                    $data = self::entry_type_validation($this, $new_type_name);
                    $existing_entry_name = $this->getDoctrine()->getRepository('AppBundle:EntryType')->findOneBy(array('name' => $new_type_name));

                    if ($data['success'] && count($existing_entry_name) == 0) {
                        $new_entry_type = new EntryType();
                        $new_entry_type->setName($new_type_name);

                        $em->persist($new_entry_type);
                        $em->flush();

                        $new_type_id = $new_entry_type->getId();

                        for ($cpt_fields = 0; $cpt_fields < count($new_type_fields_array); $cpt_fields++) {
                            $em = $this->getDoctrine()->getManager();

                            $new_field = new Field();
                            $new_field->setValue($new_type_fields_array[$cpt_fields]->name);
                            $new_field->setDefaultValue($new_type_fields_array[$cpt_fields]->default_name);
                            $new_field->setPlaceholder($new_type_fields_array[$cpt_fields]->placeholder);
                            $new_field->setFieldOrder($new_type_fields_array[$cpt_fields]->order);

                            $field_type_obj = $this->getDoctrine()->getRepository('AppBundle:FieldType')->findOneBy(array('id' => $new_type_fields_array[$cpt_fields]->field_type));
                            $new_field->setFieldType($field_type_obj);

                            $entry_type_obj = $this->getDoctrine()->getRepository('AppBundle:EntryType')->findOneBy(array('id' => $new_type_id));
                            $new_field->setEntryType($entry_type_obj);

                            $em->persist($new_field);
                            $em->flush();
                        }
                    } else if (count($existing_entry_name) > 0) {
                        $data[count($data) + 1]['name'] = 'This Name has been already registered';
                        $data['success'] = FALSE;
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($type_action == 'Delete') {
                    // *************************
                    // * Delete selected types *
                    // *************************
                    $em = $this->getDoctrine()->getManager();

                    $selected_types = $request->request->get('selected_types');

                    for ($cpt_types = 0; $cpt_types < count($selected_types); $cpt_types++) {
                        $current_type = $em->getRepository('AppBundle:EntryType')->find($selected_types[$cpt_types]);
                        $em->remove($current_type);
                        $em->flush();
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => 'success'
                    ));

                    return $response;
                } else if ($type_action == 'GetType') {
                    // **************************
                    // * Get selected type info *
                    // **************************
                    $current_type_id = $request->request->get('type_id');

                    $entry_type_obj = $this->getDoctrine()->getRepository('AppBundle:EntryType')->findOneBy(array('id' => $current_type_id));
                    $all_fields_type_obj = $this->getDoctrine()->getRepository('AppBundle:Field')->findBy(array('entryType' => $entry_type_obj), array('fieldOrder' => 'ASC'));

                    $all_fields_type_array = array();

                    for ($cpt_fields = 0; $cpt_fields < count($all_fields_type_obj); $cpt_fields++) {
                        $all_fields_type_array[$cpt_fields]['id'] = $all_fields_type_obj[$cpt_fields]->getId();
                        $all_fields_type_array[$cpt_fields]['value'] = $all_fields_type_obj[$cpt_fields]->getValue();
                        $all_fields_type_array[$cpt_fields]['default_value'] = $all_fields_type_obj[$cpt_fields]->getDefaultValue();
                        $all_fields_type_array[$cpt_fields]['placeholder'] = $all_fields_type_obj[$cpt_fields]->getPlaceholder();
                        $all_fields_type_array[$cpt_fields]['order'] = $all_fields_type_obj[$cpt_fields]->getFieldOrder();
                        $all_fields_type_array[$cpt_fields]['field_type'] = $all_fields_type_obj[$cpt_fields]->getFieldType()->getId();
                    }

                    $response = new JsonResponse();

                    $data = array();
                    $data['name'] = $entry_type_obj->getName();
                    $data['id'] = $entry_type_obj->getId();


                    $data['fields'] = $all_fields_type_array;

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($type_action == 'Edit') {
                    // **********************
                    // * Edit selected type *
                    // **********************
                    $em = $this->getDoctrine()->getManager();

                    $edit_type_fields_array = json_decode($request->request->get('edit_type_json'));
                    $edit_type_name = $request->request->get('edit_type_name');
                    $edit_type_id = $request->request->get('edit_type_id');

                    // **************
                    // * Validation *
                    // **************
                    $data = self::entry_type_validation($this, $edit_type_name);

                    $existing_type_name_from_current_id = $this->getDoctrine()->getRepository('AppBundle:EntryType')->findOneBy(array('id' => $edit_type_id))->getName();
                    $existing_type_name = $this->getDoctrine()->getRepository('AppBundle:EntryType')->findOneBy(array('name' => $edit_type_name));

                    if ($data['success'] && (count($existing_type_name) == 0 || $edit_type_name == $existing_type_name_from_current_id)) {

                        $current_entry_type = $em->getRepository('AppBundle:EntryType')->find($edit_type_id);
                        $current_entry_type->setName($edit_type_name);
                        $em->flush();

                        for ($cpt_fields = 0; $cpt_fields < count($edit_type_fields_array); $cpt_fields++) {
                            $current_field_type = $em->getRepository('AppBundle:Field')->findOneBy(array('id' => $edit_type_fields_array[$cpt_fields]->id));
                            $current_field_type->setValue($edit_type_fields_array[$cpt_fields]->name);
                            $current_field_type->setDefaultValue($edit_type_fields_array[$cpt_fields]->default_name);
                            $current_field_type->setPlaceholder($edit_type_fields_array[$cpt_fields]->placeholder);
                            $current_field_type->setFieldOrder($edit_type_fields_array[$cpt_fields]->order);
                            $em->flush();
                        }
                    } else if (count($existing_type_name) > 0 && $edit_type_name != $existing_type_name_from_current_id) {
                        $data[count($data)]['name'] = 'This Name has been already registered';
                        $data['success'] = FALSE;
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($type_action == 'field_type_list') {
                    $all_field_types = $this->getDoctrine()->getRepository('AppBundle:FieldType')->findBy(array(), array('id' => 'ASC'));

                    $all_field_types_array = array();

                    for ($cpt_types = 0; $cpt_types < count($all_field_types); $cpt_types++) {
                        $all_field_types_array[$cpt_types]['id'] = $all_field_types[$cpt_types]->getId();
                        $all_field_types_array[$cpt_types]['name'] = $all_field_types[$cpt_types]->getName();
                    }

                    $response = new JsonResponse();

                    if ($cpt_types > 0) {
                        $data = $all_field_types_array;
                    } else {
                        $data = array();
                    }

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                }
            } else {
                // *************************
                // * Types page generation *
                // *************************
                return self::render_types($this, $user_session_id, $user_session_admin, $user_session_fullname);

            }
        } else {
            if ($user_session_id > 0 && !$user_session_admin) {
                return $this->redirectToRoute('entries');
            } else {
                return $this->redirectToRoute('login');
            }
        }
    }

    // *************************
    // * Types page generation *
    // *************************
    protected static function render_types($types_content, $user_session_id, $user_session_admin, $user_session_fullname) {

        $all_entry_types = $types_content->getDoctrine()->getRepository('AppBundle:EntryType')->findBy(array(), array('name' => 'ASC'));

        $array_all_types = array();

        for ($cpt_types = 0; $cpt_types < count($all_entry_types); $cpt_types++) {
            $array_all_types[$cpt_types]['id'] = $all_entry_types[$cpt_types]->getId();
            $array_all_types[$cpt_types]['name'] = $all_entry_types[$cpt_types]->getName();

            $current_entry_record = $types_content->getDoctrine()->getRepository('AppBundle:Entry')->findOneBy(array('entryType' => $all_entry_types[$cpt_types]->getId()));
            $array_all_types[$cpt_types]['used'] = (count($current_entry_record) > 0) ? TRUE : FALSE;
        }

        return $types_content->render('types.html.twig', array(
            'current_category' => 'Types',
            'all_entry_types' => $array_all_types,
            'user_session_id' => $user_session_id,
            'user_session_admin' => $user_session_admin,
            'user_session_fullname' => $user_session_fullname
        ));
    }

    // ***********************
    // * New Tyoe Validation *
    // ***********************
    protected static function entry_type_validation($entry_type_content, $new_entry_type_name) {

        $new_entry_type = new EntryType();
        $new_entry_type->setName($new_entry_type_name);

        $validator = $entry_type_content->get('validator');
        $errors = $validator->validate($new_entry_type);

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