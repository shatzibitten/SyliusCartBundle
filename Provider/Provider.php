<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CartBundle\Provider;

use Sylius\Bundle\CartBundle\Model\CartInterface;

/**
 * Container for cart.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
final class Provider
{
    private $cart;
    
    public function getCart()
    {
        return $this->cart;
    }
    
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
    }
}