<?php

namespace AppBundle\Repository;

use \Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EntryRepository")
 */

class EntryRepository extends EntityRepository
{
    public function searchEntryRecords($parameter, $user_session_id, $user_session_admin) {

        $connection = $this->getEntityManager()->getConnection();

        // *******************************
        // * Get an array of user groups *
        // *******************************
        $sql_user_groups = "SELECT a.alibapassgroup_id FROM alibapassuser_alibapassgroup a WHERE a.alibapassuser_id = ?";

        $statement_user_groups = $connection->prepare($sql_user_groups);
        $statement_user_groups->bindValue(1, $user_session_id);
        $statement_user_groups->execute();

        $array_user_groups_results = $statement_user_groups->fetchAll();
        $array_user_groups = array();

        for ($cpt_ug = 0; $cpt_ug < count($array_user_groups_results); $cpt_ug++) {
            $array_user_groups[$cpt_ug] = $array_user_groups_results[$cpt_ug]['alibapassgroup_id'];
        }

        // **********
        // * Search *
        // **********
        if (!empty($parameter)) {
            $sql = "SELECT e.id, (c.name) AS company, (t.name) AS entrytype, e.comment, e.userfullname " .
                   "FROM entry e, company c, entry_type t " .
                   "WHERE c.id = e.company_id AND t.id = e.entry_type_id AND " .
                   "(c.name LIKE CONCAT('%', :parameter, '%') OR e.comment LIKE CONCAT('%', :parameter, '%')) " .
		   "ORDER BY company ASC";
        } else {
            $sql = "SELECT e.id, (c.name) AS company, (t.name) AS entrytype, e.comment, e.userfullname " .
                   "FROM entry e, company c, entry_type t " .
                   "WHERE c.id = e.company_id AND t.id = e.entry_type_id " .
		   "ORDER BY company ASC";
        }



        $statement = $connection->prepare($sql);

        if (!empty($parameter)) {
            $statement->bindValue('parameter', str_replace(array('+', '%', '_'), array('++', '+%', '+_'), $parameter));
        }

        $statement->execute();

        $array_results = $statement->fetchAll();

        for ($cpt_results = 0; $cpt_results < count($array_results); $cpt_results++) {
            $sql_groups = "SELECT g.id, g.name FROM alibapass_group g, entry_alibapassgroup e WHERE g.id = e.alibapassgroup_id AND e.entry_id = ?";
            $statement_groups = $connection->prepare($sql_groups);
            $statement_groups->bindValue(1, $array_results[$cpt_results]['id']);
            $statement_groups->execute();

            $current_groups = $statement_groups->fetchAll();
            $current_string_groups_names = '';
            $current_string_groups_ids = '';
            $current_array_group_ids = array();

            for ($cpt_groups = 0; $cpt_groups < count($current_groups); $cpt_groups++) {
                if ($cpt_groups > 0) {
                    $current_string_groups_names .= ', ';
                    $current_string_groups_ids .= ',';
                }

                $current_string_groups_names .= $current_groups[$cpt_groups]['name'];
                $current_string_groups_ids .= $current_groups[$cpt_groups]['id'];
                $current_array_group_ids[$cpt_groups] = $current_groups[$cpt_groups]['id'];
            }

            if (count(array_intersect($current_array_group_ids, $array_user_groups)) > 0 || $user_session_admin) {
                $array_results[$cpt_results]['allowed'] = TRUE;
            } else {
                $array_results[$cpt_results]['allowed'] = FALSE;
            }

            $array_results[$cpt_results]['alibapassgroup_names'] = $current_string_groups_names;
            $array_results[$cpt_results]['alibapassgroup_ids'] = $current_string_groups_ids;

            // error_log($array_results[$cpt_results]['allowed']);
        }

        // error_log(print_r($array_results, true));

        return $array_results;
    }
}
