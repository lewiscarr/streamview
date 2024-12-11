<?php
class restore_format_streamview_plugin extends restore_format_plugin {
    /**
     * Defines structure step to restore course format data.
     */
    protected function define_course_plugin_structure() {
        $paths = array();

        // Add custom restore paths if necessary
        // $paths[] = new restore_path_element('custom_field', '/course/format/streamview/custom_field');

        return $paths;
    }

    /**
     * Process the 'custom_field' element (if defined in define_course_plugin_structure())
     */
    // public function process_custom_field($data) {
    //     global $DB;
    //
    //     $data = (object)$data;
    //     $oldid = $data->id;
    //
    //     $data->courseid = $this->task->get_courseid();
    //
    //     $newitemid = $DB->insert_record('format_streamview_custom', $data);
    //     $this->set_mapping('format_streamview_custom', $oldid, $newitemid);
    // }
}