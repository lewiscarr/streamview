<?php
namespace format_streamview\output;

defined('MOODLE_INTERNAL') || die();

use context_course;
use core_courseformat\output\section_renderer;
use html_writer;
use moodle_url;
use stdClass;
use course_modinfo;

class renderer extends section_renderer {
    public function render_content($widget) {
        global $PAGE, $COURSE;

        // Generate a unique identifier for this instance
        $uniqueId = 'streamview-' . $COURSE->id;

        $data = $widget->export_for_template($this);
        $data->uniqueId = $uniqueId;  // Add the unique ID to the template data

        // Add section management data
        if ($PAGE->user_is_editing()) {
            $courseformat = course_get_format($COURSE);
            $modinfo = get_fast_modinfo($COURSE);

            // Add URL for adding new sections
            $addsectionurl = new moodle_url('/course/changenumsections.php',
                array('courseid' => $COURSE->id,
                      'insertsection' => 0,
                      'sesskey' => sesskey(),
                      'returnurl' => $PAGE->url->out_as_local_url(false))
            );
            $data->addnewsectionurl = $addsectionurl->out(false);
            $data->numsections = true;

            // Add section control data
            $coursecontext = context_course::instance($COURSE->id);
            $hasmanageactivities = has_capability('moodle/course:manageactivities', $coursecontext);

            foreach ($data->sections as $key => $section) {
                if (!isset($section->section)) {
                    continue;
                }
                
                $sectioninfo = $modinfo->get_section_info($section->section);
                if (!$sectioninfo) {
                    continue;
                }
                
                if ($courseformat->is_section_visible($sectioninfo)) {
                    $data->sections[$key]->hascontrol = true;
                    $data->sections[$key]->controlmenu = $this->section_edit_control_menu($COURSE, $section, $sectioninfo);
                    
                    // Add activity chooser if user can manage activities
                    if ($hasmanageactivities) {
                        $data->sections[$key]->hasmanageactivities = true;
                        $data->sections[$key]->addresourcemodchooser = $this->course_section_add_cm_control($COURSE, $section->section, 0);
                    }
                }
            }
        }

        return $this->render_from_template('format_streamview/content', $data);
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $sectioninfo Section info object from modinfo
     * @return string HTML to output.
     */
    protected function section_edit_control_menu($course, $section, $sectioninfo) {
        global $CFG;

        if ($section->section === 0) {
            return '';
        }

        $coursecontext = context_course::instance($course->id);

        if (!has_capability('moodle/course:update', $coursecontext)) {
            return '';
        }

        $controls = array();

        if (has_capability('moodle/course:update', $coursecontext)) {
            $url = new moodle_url('/course/editsection.php', array(
                'id' => $sectioninfo->id,
                'sr' => $section->section,
                'returnurl' => $this->page->url->out_as_local_url(false)
            ));
            $controls['edit'] = array(
                'url' => $url,
                'icon' => 'i/settings',
                'name' => get_string('editsection', 'format_streamview'),
                'pixattr' => array('class' => ''),
                'attr' => array('class' => 'icon edit')
            );
        }

        if (has_capability('moodle/course:delete', $coursecontext)) {
            $url = new moodle_url('/course/editsection.php', array(
                'id' => $sectioninfo->id,
                'sr' => $section->section,
                'delete' => 1,
                'returnurl' => $this->page->url->out_as_local_url(false)
            ));
            $controls['delete'] = array(
                'url' => $url,
                'icon' => 'i/delete',
                'name' => get_string('deletesection', 'format_streamview'),
                'pixattr' => array('class' => ''),
                'attr' => array('class' => 'icon delete')
            );
        }

        $menu = new stdClass();
        $menu->controls = $controls;
        return $this->render_from_template('core/action_menu', $menu);
    }
}