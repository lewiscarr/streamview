<?php
namespace format_streamview\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class activity_image extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Add hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        // Add file manager for image upload
        $mform->addElement('filemanager', 'activity_image', 
            get_string('activity_image', 'format_streamview'), 
            null, 
            array(
                'maxbytes' => 5242880,
                'accepted_types' => array('.jpg', '.jpeg', '.png'),
                'maxfiles' => 1
            )
        );

        $this->add_action_buttons();
    }
} 