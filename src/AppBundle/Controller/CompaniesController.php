<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Company;
use Symfony\Component\HttpFoundation\Session\Session;

class CompaniesController extends Controller
{
    /**
     * @Route("/companies/"), name="companies", options={"expose"=true})
     */
    public function companiesAction(Request $request) {

        $session = new Session();

        $user_session_id = $session->get('user_id');
        $user_session_admin = $session->get('admin');
        $user_session_fullname = $session->get('fullname');

        if ($user_session_id > 0) {
            // *******************************
            // * Treatment Add a new Compamy *
            // *******************************
            if ($request->isXmlHttpRequest()) {

                $company_action = $request->request->get('company_action');

                if ($company_action == 'Create') {
                    $new_company_name = $request->request->get('new_company_name');

                    $response = new JsonResponse();

                    $data = self::company_validation($this, $new_company_name);

                    $existing_company_name = $this->getDoctrine()->getRepository('AppBundle:Company')->findOneBy(array('name' => $new_company_name));

                    // **********************************
                    // * Record new Company in Database *
                    // **********************************
                    if ($data['success'] && count($existing_company_name) == 0) {
                        $new_company = new Company();
                        $new_company->setName($new_company_name);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($new_company);
                        $em->flush();
                    } else if (count($existing_company_name) > 0) {
                        $data[count($data) + 1]['name'] = 'This Name has been already registered';
                        $data['success'] = FALSE;
                    }

                    $response->setData(array(
                        'data' => $data
                    ));

                    return $response;
                } else if ($company_action == 'Delete') {

                    // *****************************
                    // * Delete selected companies *
                    // *****************************
                    $em = $this->getDoctrine()->getManager();

                    $selected_companies = $request->request->get('selected_companies');

                    for ($cpt_companies = 0; $cpt_companies < count($selected_companies); $cpt_companies++) {
                        $current_company = $em->getRepository('AppBundle:Company')->find($selected_companies[$cpt_companies]);

                        $em->remove($current_company);
                        $em->flush();
                    }

                    $response = new JsonResponse();

                    $response->setData(array(
                        'data' => 'success'
                    ));

                    return $response;
                } else if ($company_action == 'Edit') {

                    // *************************
                    // * Edit selected company *
                    // *************************
                    $em = $this->getDoctrine()->getManager();

                    $edit_company_name = $request->request->get('edit_company_name');
                    $edit_company_id = $request->request->get('edit_company_id');

                    // **************
                    // * Validation *
                    // **************
                    $data = self::company_validation($this, $edit_company_name);

                    $existing_company_name_from_current_id = $this->getDoctrine()->getRepository('AppBundle:Company')->findOneBy(array('id' => $edit_company_id))->getName();
                    $existing_company_name = $this->getDoctrine()->getRepository('AppBundle:Company')->findOneBy(array('name' => $edit_company_name));

                    if ($data['success'] && (count($existing_company_name) == 0 || $edit_company_name == $existing_company_name_from_current_id)) {
                        $current_compamy = $em->getRepository('AppBundle:Company')->find($edit_company_id);
                        $current_compamy->setName($edit_company_name);
                        $em->flush();
                    } else if (count($existing_company_name) > 0 && $edit_company_name != $existing_company_name_from_current_id) {
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
                // ****************************
                // * Companie page generation *
                // ****************************
                return self::render_companies($this, $user_session_id, $user_session_admin, $user_session_fullname);

            }
        } else {
            return $this->redirectToRoute('login');
        }
    }
    
    // ****************************
    // * Companie page generation *
    // ****************************
    protected static function render_companies($companies_content, $user_session_id, $user_session_admin, $user_session_fullname) {
        $array_all_companies = array();

        $all_companies = $companies_content->getDoctrine()->getRepository('AppBundle:Company')->findBy(array(), array('name' => 'ASC'));

        for ($cpt_companies = 0; $cpt_companies < count($all_companies); $cpt_companies++) {
            $array_all_companies[$cpt_companies]['id'] = $all_companies[$cpt_companies]->getId();
            $array_all_companies[$cpt_companies]['name'] = $all_companies[$cpt_companies]->getName();

            $current_company_record = $companies_content->getDoctrine()->getRepository('AppBundle:Entry')->findOneBy(array('company' => $all_companies[$cpt_companies]->getId()));
            $array_all_companies[$cpt_companies]['used'] = (count($current_company_record) > 0) ? TRUE : FALSE;
        }

        return $companies_content->render('companies.html.twig', array(
            'current_category' => 'Customers/Sites',
            'companies' => $array_all_companies,
            'user_session_id' => $user_session_id,
            'user_session_admin' => $user_session_admin,
            'user_session_fullname' => $user_session_fullname
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
            }

            $array_errors['success'] = FALSE;
        } else {
            $array_errors['success'] = TRUE;
        }

        return $array_errors;
    }
}

