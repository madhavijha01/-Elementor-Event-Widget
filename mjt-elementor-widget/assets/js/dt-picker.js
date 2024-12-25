jQuery(document).ready(function($) {
    $('#dt_picker_field').datetimepicker({
        dateFormat: 'yy-mm-dd', // Customize date format
        timeFormat: 'HH:mm:ss', // Customize time format
        controlType: 'select',
        oneLine: true
    });
});