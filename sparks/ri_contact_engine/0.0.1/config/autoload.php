<?php
// Load the contact-engine config files when the spark is loaded
$autoload['config'] = array('person', 'organization', 'location');

# Load a contact-engine helpers
$autoload['helpers'] = array('ce_helper');

# Load a contact-engine library
$autoload['libraries'] = array('ce');

# Load a contact-engine model
$autoload['model'] = array('ce_return_object', 'objectcommon', 'person', 'organization', 'location', 'contact');