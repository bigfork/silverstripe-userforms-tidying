<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;

class UserDefinedFormExtension extends Extension
{
    /**
     * Prevent users creating UserDefinedForm pages directly - we use the form content block instead
     *
     * @return bool
     */
    public function canCreate(): bool
    {
        return false;
    }
}
