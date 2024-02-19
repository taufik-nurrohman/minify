PHP Minify
==========

![c-s-s.php] ![h-t-m-l.php] ![j-s.php] ![j-s-o-n.php] ![p-h-p.php] ![x-m-l.php]

[c-s-s.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/c-s-s.php?branch=main&color=%234f5d95&label=c-s-s.php&labelColor=%231f2328&style=flat-square
[h-t-m-l.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/h-t-m-l.php?branch=main&color=%234f5d95&label=h-t-m-l.php&labelColor=%231f2328&style=flat-square
[j-s.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/j-s.php?branch=main&color=%234f5d95&label=j-s.php&labelColor=%231f2328&style=flat-square
[j-s-o-n.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/j-s-o-n.php?branch=main&color=%234f5d95&label=j-s-o-n.php&labelColor=%231f2328&style=flat-square
[p-h-p.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/p-h-p.php?branch=main&color=%234f5d95&label=p-h-p.php&labelColor=%231f2328&style=flat-square
[x-m-l.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/x-m-l.php?branch=main&color=%234f5d95&label=x-m-l.php&labelColor=%231f2328&style=flat-square

Motivation
----------

_TODO_

Usage
-----

This converter can be installed using [Composer](https://packagist.org/packages/taufik-nurrohman/minify), but it doesn’t
need any other dependencies and just uses Composer’s ability to automatically include files. Those of you who don’t use
Composer should be able to include the files directly into your application without any problems.

### Using Composer

From the command line interface, navigate to your project folder then run this command:

~~~ sh
composer require taufik-nurrohman/minify
~~~

Require the generated auto-loader file in your application:

~~~ php
<?php

use function x\minify\c_s_s as minify_css;
use function x\minify\h_t_m_l as minify_html;
use function x\minify\j_s as minify_js;
use function x\minify\j_s_o_n as minify_json;
use function x\minify\p_h_p as minify_php;
use function x\minify\x_m_l as minify_xml;

require 'vendor/autoload.php';

echo minify_css('asdf { asdf: 0px; } asdf { /* asdf */ }'); // Returns `'asdf{asdf:0}'`
~~~

### Using File

Require the files in your application:

~~~ php
<?php

use function x\minify\c_s_s as minify_css;
use function x\minify\h_t_m_l as minify_html;
use function x\minify\j_s as minify_js;
use function x\minify\j_s_o_n as minify_json;
use function x\minify\p_h_p as minify_php;
use function x\minify\x_m_l as minify_xml;

require 'c-s-s.php';
require 'h-t-m-l.php';
require 'j-s.php';
require 'j-s-o-n.php';
require 'p-h-p.php';
require 'x-m-l.php';

echo minify_css('asdf { asdf: 0px; } asdf { /* asdf */ }'); // Returns `'asdf{asdf:0}'`
~~~

Options
-------

_TODO_

Tests
-----

Clone this repository into the root of your web server that supports PHP and then you can open the `test/*.php` file
with your browser to see the result and the performance of this converter in various cases.

Tweaks
------

_TODO_

License
-------

This library is licensed under the [MIT License](LICENSE). Please consider
[donating 💰](https://github.com/sponsors/taufik-nurrohman) if you benefit financially from this library.