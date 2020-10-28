/**
 * Enforced settings script.
 */
jQuery(document).ready(function ($) {

  $('input[name="enforce_settings_on_subsites"]').on('change', function () {
    const wrapper = $(this).closest('fieldset').find('.postbox')
    const value = $(this).val()
    if ('some' === value) {
      wrapper.slideDown()
    } else {
      wrapper.slideUp()
    }
  })

  $('.js-mwpal-disabled-events').select2({
    data: JSON.parse(mwpal_enforced_settings.events),
    placeholder: mwpal_enforced_settings.selectEvents,
    minimumResultsForSearch: 10,
    multiple: true
  })
})
