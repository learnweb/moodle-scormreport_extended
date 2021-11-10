<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Core Report class of extended reporting plugin
 *
 * @package    scormreport_extended
 * @copyright  2021 Robin Tschudi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace scormreport_extended;

defined('MOODLE_INTERNAL') || die();

use context_module;
use core\chart_series;


/**
 * Main class to control the extended reporting
 *
 * @package    scormreport_extended
 * @copyright  2021 Robin Tschudi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class report extends \mod_scorm\report {

    const PLUGINNAME = 'scormreport_extended';

    public function get_data($scormid) {
        global $DB;
        $sql = 'SELECT (row_number() over ())-1 as index, userid, attempt, element, value FROM {scorm_scoes_track}'.
        ' WHERE scormid=' . htmlspecialchars($scormid);
        return $DB->get_records_sql($sql);
    }

    /**
     * Displays the full report.
     *
     * @param \stdClass $scorm full SCORM object
     * @param \stdClass $cm - full course_module object
     * @param \stdClass $course - full course object
     * @param string $download - type of download being requested
     * @return void
     */
    public function display($scorm, $cm, $course, $download) {
        global $DB, $OUTPUT, $PAGE;
        $download = optional_param('download', '', PARAM_ALPHA);
        $displayoptions = array();
        $table = new \flexible_table('scormdetailsreport');
        $table->is_downloading($download, 'SCORM_DATA', 'Sheet 1');
        $columns = ['userid', 'attempt', 'element', 'value'];
        $headers = [];
        $help = [];
        foreach ($columns as $column) {
            $headers[] = get_string($column, self::PLUGINNAME);
            $help[] = null;
        }

        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->define_help_for_headers($help);
        $table->sortable(true);
        $table->setup();
        $table->define_baseurl($PAGE->url);
        $data = $this->get_data($scorm->id);
        $flatteneddata = array_map(function($item) {
            return [$item->userid, $item->attempt, $item->element, $item->value];
        }, $data);
        foreach ($flatteneddata as $flatrow) {
            $table->add_data($flatrow);
        }
        if (!$table->is_downloading()) {
            $table->finish_output();
        }
    }
}
