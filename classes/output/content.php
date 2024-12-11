<?php
namespace format_streamview\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use course_modinfo;
use completion_info;
use cm_info;
use action_menu_link_secondary;
use context_course;
use moodle_url;
use context_module;

class content implements renderable, templatable {
    private $format;

    public function __construct($format) {
        $this->format = $format;
    }

    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $course = $this->format->get_course();
        $modinfo = course_modinfo::instance($course);
        $sections = $modinfo->get_section_info_all();
        $completion = new completion_info($course);
        $context = context_course::instance($course->id);

        $data = new stdClass();
        $data->sections = array();

        foreach ($sections as $section) {
            if ($section->uservisible) {
                $sectiondata = new stdClass();
                $sectiondata->id = $section->id;
                $sectiondata->section = $section->section;  // Section number
                $sectiondata->title = $this->format->get_section_name($section);
                $sectiondata->activities = array();

                // Add activity chooser if editing
                if ($PAGE->user_is_editing()) {
                    $sectiondata->hasmanageactivities = true;
                    $sectiondata->addnewactivity = $output->course_section_add_cm_control($course, $section->section);
                }

                if (!empty($modinfo->sections[$section->section])) {
                    foreach ($modinfo->sections[$section->section] as $cmid) {
                        $cm = $modinfo->cms[$cmid];
                        if ($cm->uservisible) {
                            $activity = new stdClass();
                            $activity->name = $cm->name;
                            $activity->url = $cm->url;
                            $activity->icon = $output->pix_icon('icon', $cm->modfullname, $cm->modname, array('class' => 'iconlarge'));
                            $activity->modname = $cm->modname;
                            
                            // Add completion data
                            if ($completion->is_enabled($cm)) {
                                $activity->completion = new stdClass();
                                $activity->completion->tracking = true;
                                $completiondata = $completion->get_data($cm, true);
                                $activity->completion->state = $completiondata->completionstate;
                                $activity->completion->isManual = $cm->completion == COMPLETION_TRACKING_MANUAL;
                                $activity->completion->isAutomatic = $cm->completion == COMPLETION_TRACKING_AUTOMATIC;
                                $activity->completion->cmid = $cm->id;
                                $activity->completion->overallclass = 'completion-' . ($completiondata->completionstate ? 'complete' : 'incomplete');
                            }

                            // Add cover image if exists
                            $fs = get_file_storage();
                            $modcontext = context_module::instance($cm->id);
                            $coursecontext = context_course::instance($course->id);

                            $files = $fs->get_area_files($modcontext->id, 'format_streamview', 'activityimage', $cm->id, 'sortorder', false);

                            if (!$files) {
                                $files = $fs->get_area_files($coursecontext->id, 'format_streamview', 'activityimage', $cm->id, 'sortorder', false);
                            }

                            if ($files) {
                                foreach ($files as $file) {
                                    if ($file->is_valid_image()) {
                                        $activity->coverimage = moodle_url::make_pluginfile_url(
                                            $file->get_contextid(),
                                            'format_streamview',
                                            'activityimage',
                                            $cm->id,
                                            '/',
                                            $file->get_filename()
                                        )->out();
                                        break;
                                    }
                                }
                            } else {
                                $activity->coverimage = null;
                            }

                            if ($PAGE->user_is_editing()) {
                                $activity->editing = $this->get_cm_edit_actions($cm, $section->section);
                            }

                            $sectiondata->activities[] = $activity;
                        }
                    }
                }

                $data->sections[] = $sectiondata;
            }
        }

        return $data;
    }

    private function get_cm_edit_actions(cm_info $cm, $sectionreturn = null) {
        $actions = course_get_cm_edit_actions($cm, $cm->indent, $sectionreturn);
        $editactions = [];

        // Add our custom image upload action first
        $uploadurl = new \moodle_url('/course/format/streamview/edit_image.php', 
            ['cmid' => $cm->id, 'id' => $cm->course]);
        
        $editactions[] = [
            'url' => $uploadurl->out(false),
            'icon' => 'i/upload', // Using Moodle's built-in upload icon
            'name' => 'Upload Image',
            'attributes' => [
                ['name' => 'class', 'value' => 'streamview-edit-action'],
                ['name' => 'title', 'value' => 'Upload Image']
            ],
        ];

        // Add the standard actions
        foreach ($actions as $action) {
            if ($action instanceof action_menu_link_secondary) {
                $editactions[] = [
                    'url' => $action->url->out(false),
                    'icon' => $action->icon->pix, // Fixed: Use icon->pix instead of pix
                    'name' => $action->text,
                    'attributes' => array_map(function($key, $value) {
                        return ['name' => $key, 'value' => $value];
                    }, array_keys($action->attributes), $action->attributes),
                ];
            }
        }

        return $editactions;
    }
}