<?php
class backup_format_streamview_plugin extends backup_format_plugin {
    /**
     * Returns the format information to attach to course element
     */
    protected function define_course_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, '/course/format', 'streamview');

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // Add custom fields if necessary
        // $pluginwrapper->add_child(new backup_nested_element('custom_field', array('id'), array('name', 'value')));

        // Define sources
        // $pluginwrapper->set_source_table('format_streamview_custom', array('courseid' => backup::VAR_COURSEID));

        return $plugin;
    }
}