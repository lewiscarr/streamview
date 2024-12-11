<?php
namespace format_streamview\output;

use core_courseformat\output\section_renderer;
use core_courseformat\base as course_format;
use section_info;
use renderer_base;
use stdClass;
use context_course;

class coursesectionrenderer extends section_renderer {
    public function render_section(stdClass $section, $onsectionpage, $sectionreturn) {
        $format = $this->courseformat;
        $course = $format->get_course();
        $context = context_course::instance($course->id);

        $data = new stdClass();
        $data->section = $section;
        $data->onsectionpage = $onsectionpage;
        $data->sectionreturn = $sectionreturn;
        $data->courseformat = $format;
        $data->activities = $this->render_activities($section);

        return $this->render_from_template('format_streamview/section', $data);
    }

    protected function render_activities($section) {
        global $PAGE;

        $output = '';
        $modinfo = get_fast_modinfo($this->course);
        $sectionmods = $modinfo->sections[$section->section] ?? [];

        foreach ($sectionmods as $modnumber) {
            $mod = $modinfo->cms[$modnumber];
            
            if (!$mod->is_visible_on_course_page()) {
                continue;
            }

            // Get all the editing actions/controls
            $actions = [];
            if ($PAGE->user_is_editing()) {
                // Get standard controls first
                $controls = $this->course_section_cm_controls($this->course, $section, $mod);
                
                // Add our custom upload control at the start
                $uploadurl = new moodle_url('/course/format/streamview/edit_image.php', 
                    ['cmid' => $mod->id, 'id' => $this->course->id]);
                
                array_unshift($controls, [
                    'url' => $uploadurl,
                    'icon' => 'fa-upload',
                    'text' => get_string('uploadimage', 'format_streamview'),
                    'attributes' => ['class' => 'streamview-edit-action']
                ]);

                // Convert controls to actions
                if (!empty($controls)) {
                    foreach ($controls as $control) {
                        $actions[] = [
                            'url' => $control['url'],
                            'icon' => $this->render_action_icon($control)
                        ];
                    }
                }
            }

            $data = [
                'url' => $mod->url,
                'icon' => $mod->get_icon_url(),
                'name' => $mod->name,
                'actions' => $actions
            ];

            $output .= $this->render_from_template('format_streamview/activity', $data);
        }

        return $output;
    }

    // Helper method to render action icons (if you don't already have this)
    protected function render_action_icon($action) {
        if (isset($action['icon'])) {
            if (strpos($action['icon'], 'fa-') !== false) {
                // For Font Awesome icons
                return html_writer::tag('i', '', [
                    'class' => 'icon fa ' . $action['icon'] . ' fa-fw',
                    'title' => $action['text'],
                    'role' => 'img',
                    'aria-label' => $action['text']
                ]);
            } else {
                // For other icons
                return $action['icon'];
            }
        }
        return '';
    }

    protected function course_section_cm_controls($course, $section, $mod) {
        $controls = parent::course_section_cm_controls($course, $section, $mod);

        // Add custom upload image action
        $uploadurl = new moodle_url('/course/format/streamview/edit_image.php', 
            ['cmid' => $mod->id, 'id' => $course->id]);
        
        $controls[] = [
            'url' => $uploadurl,
            'icon' => 'fa-upload',
            'text' => get_string('uploadimage', 'format_streamview'),
            'attributes' => ['class' => 'streamview-edit-action']
        ];

        return $controls;
    }
}