<?php
/**
 * EvaCrawler
 *
 * @link      https://github.com/AlloVince/Evacrawler
 * @copyright Copyright (c) 2012-2013 AlloVince (http://avnpc.com/)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @author    AlloVince
 */

namespace EvaCrawler;

use Zend\Config\Config;

class Channel
{
    protected $url;

    protected $format;

    protected $visibility = 'public';

    protected $authority = '';

    protected $pager = false;

    protected $updateInterval = 0;

}
