<?php
// Load the contact-engine config files when the spark is loaded
$autoload['config'] = array('person', 'organization', 'location');

# Load a contact-engine library
$autoload['libraries'] = array('ce');

# Load a contact-engine model
$autoload['model'] = array('objectcommon', 'person', 'organization', 'location');