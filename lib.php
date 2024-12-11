<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/format/lib.php');

class format_streamview extends core_courseformat\base {
    public function get_view_url($section, $options = array()) {
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                // Use get_course_display() method if available, otherwise default to COURSE_DISPLAY_SINGLEPAGE
                $usercoursedisplay = method_exists($this, 'get_course_display') 
                    ? $this->get_course_display() 
                    : COURSE_DISPLAY_SINGLEPAGE;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                $url->set_anchor('section-'.$sectionno);
            }
        }
        return $url;
    }

    public function uses_sections() {
        return true;
    }

    public function uses_indentation(): bool {
        return false;
    }

    public function get_output_classname(string $outputname): string {
        if ($outputname === 'content') {
            return 'format_streamview\output\content';
        }
        return parent::get_output_classname($outputname);
    }
}

/**
 * Serves file from the format_streamview file areas
 *
 * @param stdClass $course The course object
 * @param stdClass $cm The course module object
 * @param stdClass $context The context
 * @param string $filearea The name of the file area
 * @param array $args Extra arguments
 * @param bool $forcedownload Whether or not force download
 * @param array $options Additional options affecting the file serving
 * @return bool False if file not found, does not return if found - just send the file
 */
function format_streamview_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    require_login($course);

    if ($filearea !== 'activityimage') {
        return false;
    }

    if ($context->contextlevel != CONTEXT_COURSE && $context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    $itemid = array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    $fs = get_file_storage();
    
    $file = $fs->get_file($context->id, 'format_streamview', $filearea, $itemid, $filepath, $filename);
    
    if (!$file && $context->contextlevel == CONTEXT_COURSE) {
        $modcontext = context_module::instance($itemid);
        $file = $fs->get_file($modcontext->id, 'format_streamview', $filearea, $itemid, $filepath, $filename);
    }

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}