== Changelog ==
= 1.03 =
* Switched to MailChimp API v2.0
* Added an api key check on the settings page that holds the key

= 1.02.01 =
* Fixed synching in combination with Formidable v1.07.02
* Fixed synching when using a user ID field for the email

= 1.02 =
* Only load MailChimp settings when they are needed
* Removed constants for reduced overhead

= 1.01 =
* Fixed more strict error messages
* UI improvements
* Formidable v1.07.02 compatibility
* Eliminated unnecessary globals

= 1.0 =
* Fixed auto updating when used with Formidable 1.07+
* Fixed strict error messages
* Added frm_mlcmp_update_existing hook for allowing/disallowing editing
* Added frm_mlcmp_send_welcome hook for turning the welcome email on/off
* Send dates in the format determined by MC settings

= 1.0rc1 =
* Send checkboxes to MailChimp as a comma-separated list instead of an array
* Send dates in yyyy-mm-dd format
* Remove conflict with saving settings if caching plugins are activated
* Allow empty group settings
* Update conditional logic to allow fields with separate values

= 1.0b3 =
* Added automatic syncing to update MailChimp users when the Formidable record is updated
* Fixed issue causing multiple list settings to not save for all users

= 1.0b2 =
* REQUIRES v1.6
* Changed MCAPI class name to prevent MailChimp API version conflicts
* Added support for custom groups