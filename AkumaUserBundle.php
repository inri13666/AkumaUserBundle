<?php

namespace Akuma\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkumaUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
