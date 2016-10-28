<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Archive;
use AppBundle\Entity\Entry;
use AppBundle\Entity\EntryField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Company;
use Symfony\Component\HttpFoundation\Session\Session;


class EntriesController extends Controller
{
    /**
     * @Route("/entries/"), name="entries", options={"expose"=true})
     * @Route("/entries/{string_search}"), name="search", options={"expose"=true})
     */
    public function entriesAction(Request $request, $string_search = NULL)
    {
        $session = new Session();

        $user_session_id = $session->get('user_id');
        $user_session_admin = $session->get('admin');
        $user_session_fullname = $session->get('fullname');

        if ($user_session_id > 0) {

            if ($request->isXmlHttpRequest()) {

                $entry_action = $request->request->get('entry_action');

                // ************
                // * Get Data *
                // ************
                if ($entry_action == 'GetData') {
                    $current_type_id = $request->request->get('type_id');

                    // Fields from selected entry type
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

                    $data['fields'] = $all_fields_type_array;

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($entry_action == 'New') {
                    $em = $this->getDoctrine()->getManager();

                    $id_company = $request->request->get('id_company');
                    $new_company_name = $request->request->get('new_company_name');
                    $id_entry_type = $request->request->get('id_entry_type');
                    $entry_comment = $request->request->get('entry_comment');
                    $entry_groups = $request->request->get('entry_groups');
                    $field_ids_array = $request->request->get('field_ids');
                    $field_types_array = $request->request->get('field_types');
                    $field_values_array = $request->request->get('field_values');

                    $response = new JsonResponse();

                    if ($id_company < 1) {
                        $data = self::company_validation($this, $new_company_name);

                        $existing_company_name = $this->getDoctrine()->getRepository('AppBundle:Company')->findOneBy(array('name' => $new_company_name));

                        // **********************************
                        // * Record new Company in Database *
                        // **********************************
                        if ($data['success'] && count($existing_company_name) == 0) {
                            $new_company = new Company();
                            $new_company->setName($new_company_name);

                            $em->persist($new_company);
                            $em->flush();
                            $id_company = $new_company->getId();
                        } else if (count($existing_company_name) > 0) {
                            $data[count($data) + 1]['name'] = 'This Name has been already registered';
                            $data['success'] = FALSE;
                        }
                    } else {
                        $data['success'] = TRUE;
                    }

                    if ($data['success'] != FALSE) {

                        // *********************
                        // * Record Other Data *
                        // *********************
                        $new_entry = new Entry();

                        $company_obj = $this->getDoctrine()->getRepository('AppBundle:Company')->findOneBy(array('id' => $id_company));
                        $new_entry->setCompany($company_obj);
                        $entry_type_obj = $this->getDoctrine()->getRepository('AppBundle:EntryType')->findOneBy(array('id' => $id_entry_type));
                        $new_entry->setEntryType($entry_type_obj);
                        $new_entry->setComment($entry_comment);

                        if (is_array($entry_groups)) {
                            foreach ($entry_groups as $entry_group) {
                                if ($entry_group > 0) {
                                    $current_group = $this->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->find($entry_group);

                                    $new_entry->addAlibapassgroup($current_group);
                                }
                            }
                        }

                        $entry_user_obj = $this->getDoctrine()->getRepository('AppBundle:AlibapassUser')->findOneBy(array('id' => $user_session_id));
                        $user_full_name = $entry_user_obj->getFirstname() . ' ' . $entry_user_obj->getLastname();
                        $new_entry->setUser(3);
                        $new_entry->setUserfullname($user_full_name);
                        $new_entry->setRev(1);
                        $new_entry->setDatetimets(time());

                        $em->persist($new_entry);
                        $em->flush();

                        $id_entry = $new_entry->getId();

                        // *************************
                        // * Record fields content *
                        // *************************
                        $entry_obj = $this->getDoctrine()->getRepository('AppBundle:Entry')->findOneBy(array('id' => $id_entry));

                        for ($cpt_fields = 0; $cpt_fields < count($field_ids_array); $cpt_fields++) {
                            $new_entry_field = new EntryField();
                            $new_entry_field->setEntry($entry_obj);
                            $field_obj = $this->getDoctrine()->getRepository('AppBundle:Field')->findOneBy(array('id' => $field_ids_array[$cpt_fields]));
                            $new_entry_field->setField($field_obj);

                            if ($field_types_array[$cpt_fields] == 2) {
                                $new_entry_field->setValue(self::simple_encrypt($field_values_array[$cpt_fields]));
                            } else {
                                $new_entry_field->setValue($field_values_array[$cpt_fields]);
                            }

                            $em->persist($new_entry_field);
                            $em->flush();
                        }

                    }


                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($entry_action == 'GetDataForEdit') {
                    // ***************************************
                    // * Get Data to prior editing the Entry *
                    // ***************************************
                    $em = $this->getDoctrine()->getManager();

                    $id_entry = $request->request->get('id_entry');

                    $entry_obj = $this->getDoctrine()->getRepository('AppBundle:Entry')->findOneBy(array('id' => $id_entry));

                    $data['company'] = $entry_obj->getCompany()->getId();
                    $data['type'] = $entry_obj->getEntryType()->getId();
                    $data['comment'] = $entry_obj->getComment();
                    $groups = $entry_obj->getAlibapassGroup();

                    $array_group_ids = array();

                    for ($cpt_groups = 0; $cpt_groups < count($groups); $cpt_groups++) {
                        $array_group_ids[$cpt_groups] = $groups[$cpt_groups]->getId();
                    }

                    $data['groups'] = $array_group_ids;

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($entry_action == 'GetFields') {
                    $em = $this->getDoctrine()->getManager();

                    $current_entry_id = $request->request->get('current_entry_id');

                    $current_entryfields = $this->getDoctrine()->getRepository('AppBundle:EntryField')->findBy(array('entry' => $current_entry_id));


                    $data = array();

                    for ($cpt_entryfields = 0; $cpt_entryfields < count($current_entryfields); $cpt_entryfields++) {
                        $current_field = $this->getDoctrine()->getRepository('AppBundle:Field')->findOneBy(array('id' => $current_entryfields[$cpt_entryfields]->getField()->getId()));

                        $data['values'][$cpt_entryfields]['uid'] = $current_entryfields[$cpt_entryfields]->getId();
                        $data['values'][$cpt_entryfields]['id'] = $current_field->getId();
                        $data['values'][$cpt_entryfields]['type'] = $current_field->getFieldType()->getName();

                        if ($data['values'][$cpt_entryfields]['type'] == 'password') {
                            $data['values'][$cpt_entryfields]['value'] = self::simple_decrypt($current_entryfields[$cpt_entryfields]->getValue());
                        } else {
                            $data['values'][$cpt_entryfields]['value'] = $current_entryfields[$cpt_entryfields]->getValue();
                        }

                        $data['values'][$cpt_entryfields]['name'] = $current_entryfields[$cpt_entryfields]->getField();

                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($entry_action == 'Delete') {
                    // ******************
                    // * Delete Entries *
                    // ******************
                    $em = $this->getDoctrine()->getManager();

                    $selected_entries = $request->request->get('selected_entries');

                    for ($cpt_entries = 0; $cpt_entries < count($selected_entries); $cpt_entries++) {
                        $current_entry = $em->getRepository('AppBundle:Entry')->find($selected_entries[$cpt_entries]);

                        $new_archive = new Archive();
                        $new_archive->setSite($current_entry->getCompany());

                        $groups = $current_entry->getAlibapassgroup();
                        $string_groups = '';

                        for ($cpt_groups = 0; $cpt_groups < count($groups); $cpt_groups++) {
                            if ($cpt_groups > 0) {
                                $string_groups .= ', ';
                            }

                            $string_groups .= $groups[$cpt_groups]->getName();
                        }

                        $new_archive->setGroups($string_groups);
                        $new_archive->setType($current_entry->getEntryType());

                        $string_credentials = '';

                        $current_entryfields = $this->getDoctrine()->getRepository('AppBundle:EntryField')->findBy(array('entry' => $current_entry->getId()));

                        for ($cpt_entryfields = 0; $cpt_entryfields < count($current_entryfields); $cpt_entryfields++) {
                            if ($cpt_entryfields > 0) {
                                $string_credentials .= ', ';
                            }

                            $current_field = $this->getDoctrine()->getRepository('AppBundle:Field')->findOneBy(array('id' => $current_entryfields[$cpt_entryfields]->getField()->getId()));

                            $current_type = $current_field->getFieldType()->getName();

                            $string_credentials .= $current_entryfields[$cpt_entryfields]->getField() . ': ';

                            if ($current_type == 'password') {
                                $string_credentials .= self::simple_decrypt($current_entryfields[$cpt_entryfields]->getValue());
                            } else {
                                $string_credentials .= $current_entryfields[$cpt_entryfields]->getValue();
                            }
                        }

                        $new_archive->setCredentials($string_credentials);
                        $new_archive->setComment($current_entry->getComment());
                        $new_archive->setUserfullname($current_entry->getUserfullname());


                        $current_date = new \DateTime();
                        $current_date->setTimestamp(time());

                        $new_archive->setDatetimeutc($current_date);
                        $new_archive->setAction('delete');
                        $new_archive->setEntry($current_entry->getId());
                        $new_archive->setRevision($current_entry->getRev());


                        for ($cpt_entryfields = 0; $cpt_entryfields < count($current_entryfields); $cpt_entryfields++) {
                            $current_field_to_remove = $em->getRepository('AppBundle:EntryField')->find($current_entryfields[$cpt_entryfields]->getId());
                            $em->remove($current_field_to_remove);
                        }

                        $em->persist($new_archive);
                        $em->remove($current_entry);
                        $em->flush();
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => 'success'
                    ));

                    return $response;
                } else if ($entry_action == 'Edit') {
                    // **************
                    // * Edit Entry *
                    // **************
                    $em = $this->getDoctrine()->getManager();

                    $id_entry_edit = $request->request->get('id_entry_edit');
                    $array_field_types = $request->request->get('array_field_types');
                    $array_field_ids = $request->request->get('array_field_ids');
                    $array_field_values = $request->request->get('array_field_values');
                    $edit_groups = $request->request->get('edit_groups');
                    $comment_entry_edit = $request->request->get('comment_entry_edit');

                    $current_entry_edit = $em->getRepository('AppBundle:Entry')->find($id_entry_edit);
                    $current_entry_edit->setComment($comment_entry_edit);

                    for ($cpt_ids = 0; $cpt_ids < count($array_field_ids); $cpt_ids++) {
                        $current_entryfield = $this->getDoctrine()->getRepository('AppBundle:EntryField')->findOneBy(array('field' => $array_field_ids[$cpt_ids], 'entry' => $id_entry_edit));

                        if ($array_field_types[$cpt_ids] == 2) {
                            $current_entryfield->setValue(self::simple_encrypt($array_field_values[$cpt_ids]));
                        } else {
                            $current_entryfield->setValue($array_field_values[$cpt_ids]);
                        }
                    }

                    // **********************************
                    // * First delete associated Groups *
                    // **********************************
                    $current_entry_groups = $current_entry_edit->getAlibapassgroup();

                    foreach ($current_entry_groups as $current_entry_group) {
                        $current_entry_edit->removeAlibapassgroup($current_entry_group);
                    }

                    // ******************************
                    // * Then add associated groups *
                    // ******************************
                    if (is_array($edit_groups)) {
                        foreach ($edit_groups as $edit_group) {
                            $current_group = $this->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->find($edit_group);

                            $current_entry_edit->addAlibapassgroup($current_group);
                        }
                    }

                    $em->flush();

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => 'success'
                    ));

                    return $response;
                }
            } else {
                return self::render_entries($this, $user_session_id, $user_session_admin, $user_session_fullname, $string_search);
            }
        } else {
            return $this->redirectToRoute('login');
        }
    }

    protected static function render_entries($entries_content, $user_session_id, $user_session_admin, $user_session_fullname, $string_search) {

        // $custom_query = $entries_content->getDoctrine()->getRepository('AppBundle:Entry')->searchEntryRecords('test');

        // error_log(print_r($custom_query, true));

        $em = $entries_content->getDoctrine()->getManager();
        // $search_results = $em->getRepository('AppBundle:Entry')->searchEntryRecords('test');
        
        $all_companies = $entries_content->getDoctrine()->getRepository('AppBundle:Company')->findBy(array(), array('name' => 'ASC'));
        $all_entrytypes = $entries_content->getDoctrine()->getRepository('AppBundle:EntryType')->findBy(array(), array('name' => 'ASC'));
        $all_groups = $entries_content->getDoctrine()->getRepository('AppBundle:AlibapassGroup')->findBy(array(), array('name' => 'ASC'));
        // $all_entries_doctrine = $entries_content->getDoctrine()->getRepository('AppBundle:Entry')->findBy(array(), array('company' => 'ASC'));

        $all_entries_search = $entries_content->getDoctrine()->getRepository('AppBundle:Entry')->searchEntryRecords($string_search, $user_session_id, $user_session_admin);

        $all_entries = array();

        for ($cpt_entries = 0; $cpt_entries < count($all_entries_search); $cpt_entries++) {

            $all_entries[$cpt_entries]['id'] = $all_entries_search[$cpt_entries]['id'];
            $all_entries[$cpt_entries]['company'] = $all_entries_search[$cpt_entries]['company'];
            $all_entries[$cpt_entries]['entrytype'] = $all_entries_search[$cpt_entries]['entrytype'];
            $all_entries[$cpt_entries]['userfullname'] = $all_entries_search[$cpt_entries]['userfullname'];
            $all_entries[$cpt_entries]['comment'] = $all_entries_search[$cpt_entries]['comment'];
            $all_entries[$cpt_entries]['alibapassgroup_ids'] = $all_entries_search[$cpt_entries]['alibapassgroup_ids'];
            $all_entries[$cpt_entries]['alibapassgroup_names'] = $all_entries_search[$cpt_entries]['alibapassgroup_names'];
            $all_entries[$cpt_entries]['allowed'] = $all_entries_search[$cpt_entries]['allowed'];

            $current_entryfields = $entries_content->getDoctrine()->getRepository('AppBundle:EntryField')->findBy(array('entry' => $all_entries[$cpt_entries]['id']));

            if (!$all_entries[$cpt_entries]['allowed']) {
                $all_entries[$cpt_entries]['nbr_fields'] = 1;
            } else {
                $all_entries[$cpt_entries]['nbr_fields'] = count($current_entryfields);
            }

            for ($cpt_entryfields = 0; $cpt_entryfields < count($current_entryfields); $cpt_entryfields++) {
                $current_field = $entries_content->getDoctrine()->getRepository('AppBundle:Field')->findOneBy(array('id' => $current_entryfields[$cpt_entryfields]->getField()->getId()));

                $all_entries[$cpt_entries]['all_fields'][$cpt_entryfields]['uid'] = $current_entryfields[$cpt_entryfields]->getId();
                $all_entries[$cpt_entries]['all_fields'][$cpt_entryfields]['type'] = $current_field->getFieldType()->getName();

                if ($all_entries[$cpt_entries]['all_fields'][$cpt_entryfields]['type'] == 'password') {
                    $all_entries[$cpt_entries]['all_fields'][$cpt_entryfields]['value'] = self::simple_decrypt($current_entryfields[$cpt_entryfields]->getValue());
                } else {
                    $current_value = $current_entryfields[$cpt_entryfields]->getValue();

                    if (substr($current_value, 0, 7) == 'http://' || substr($current_value, 0, 8) == 'https://') {
                        $all_entries[$cpt_entries]['all_fields'][$cpt_entryfields]['value'] = '<a style="max-width: 200px; overflow: hidden; word-wrap: break-word;" target="_blank" href="' . $current_value . '">' . $current_value . '</a>';
                    } else {
                        $all_entries[$cpt_entries]['all_fields'][$cpt_entryfields]['value'] = $current_value;
                    }
                }


                $all_entries[$cpt_entries]['all_fields'][$cpt_entryfields]['name'] = $current_entryfields[$cpt_entryfields]->getField();

            }

        }

        return $entries_content->render('entries.html.twig', array(
            'current_category' => 'Entries',
            'companies' => $all_companies,
            'groups' => $all_groups,
            'entrytypes' => $all_entrytypes,
            'entries' => $all_entries,
            'user_session_id' => $user_session_id,
            'user_session_admin' => $user_session_admin,
            'user_session_fullname' => $user_session_fullname,
	    'string_search' => $string_search
        ));
    }

    // **************************
    // * New Company Validation *
    // **************************
    protected static function company_validation($companies_content, $new_company_name) {
        $new_company = new Company();
        $new_company->setName($new_company_name);

        $validator = $companies_content->get('validator');
        $errors = $validator->validate($new_company);

        if (count($errors) > 0) {
            $array_errors = array();

            for ($cpt_errors = 0; $cpt_errors < count($errors); $cpt_errors++) {
                $array_errors[$cpt_errors] = array($errors[$cpt_errors]->getPropertyPath() => $errors[$cpt_errors]->getMessage());

                if (strpos($array_errors[$cpt_errors]['name'], 'blank') !== FALSE) {
                    $array_errors[$cpt_errors]['name'] .= ' Or, please select an existing Customer/Site.';
                }
            }

            $array_errors['success'] = FALSE;
        } else {
            $array_errors['success'] = TRUE;
        }

        return $array_errors;
    }

    // ***********************
    // * Password Encryption *
    // ***********************
    protected static function simple_encrypt($text)
    {
        return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, 'K3jZts6*O0x?lp!?', $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
    }

    protected static function simple_decrypt($text)
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, 'K3jZts6*O0x?lp!?', base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    }
}
