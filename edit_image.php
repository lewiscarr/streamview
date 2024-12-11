<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../../config.php');
require_once($CFG->libdir.'/formslib.php');

$cmid = required_param('cmid', PARAM_INT);
$id = required_param('id', PARAM_INT);

$context = context_module::instance($cmid);
require_login($id);

$PAGE->set_url('/course/format/streamview/edit_image.php', ['cmid' => $cmid, 'id' => $id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('editimage', 'format_streamview'));
$PAGE->set_heading(get_string('editimage', 'format_streamview'));

class edit_image_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Get draft item id
        $draftitemid = file_get_submitted_draft_itemid('activityimage');
        
        // Add file manager for image upload
        $mform->addElement('filemanager', 'activityimage', get_string('activityimage', 'format_streamview'), null, [
            'subdirs' => 0,
            'maxbytes' => 1048576, // 1MB
            'maxfiles' => 1,
            'accepted_types' => ['image']
        ]);

        // Copy existing files to draft area
        file_prepare_draft_area($draftitemid, $this->_customdata['context']->id, 'format_streamview', 'activityimage', 
            $this->_customdata['cmid'], ['subdirs' => 0, 'maxfiles' => 1]);
        
        $mform->setDefault('activityimage', $draftitemid);

        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }
}

$customdata = ['cmid' => $cmid, 'id' => $id, 'context' => $context];
$mform = new edit_image_form(null, $customdata);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $id]));
} else if ($data = $mform->get_data()) {
    file_save_draft_area_files($data->activityimage, $context->id, 'format_streamview', 'activityimage', 
        $cmid, ['subdirs' => 0, 'maxfiles' => 1]);

    cache_helper::purge_by_event('contentupdated');
    
    redirect(new moodle_url('/course/view.php', ['id' => $id]));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer(); 