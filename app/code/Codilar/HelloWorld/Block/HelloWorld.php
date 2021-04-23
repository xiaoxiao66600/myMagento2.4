<?php
/**
 *
 * @package     magento2
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        https://www.codilar.com/https://www.codilar.com/
 */

namespace Codilar\HelloWorld\Block;

use Codilar\HelloWorld\Helper\Data;
use Magento\Framework\View\Element\Template;

class HelloWorld extends Template
{
    private $helpData;

    public function __construct(Template\Context $context, array $data = [], Data $helpData)
    {
        parent::__construct($context, $data);
        $this->helpData = $helpData;
    }

    public function getText() {
        return $this->helpData->getGeneralConfig('display_text') ?? 'no config';
    }
}
