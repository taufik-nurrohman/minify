PHP Minify
==========

![c-s-s.php] ![h-t-m-l.php] ![j-s.php] ![j-s-o-n.php] ![p-h-p.php] ![x-m-l.php]

[c-s-s.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/index/c-s-s.php?branch=main&color=%234f5d95&label=c-s-s.php&labelColor=%231f2328&style=flat-square
[h-t-m-l.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/index/h-t-m-l.php?branch=main&color=%234f5d95&label=h-t-m-l.php&labelColor=%231f2328&style=flat-square
[j-s.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/index/j-s.php?branch=main&color=%234f5d95&label=j-s.php&labelColor=%231f2328&style=flat-square
[j-s-o-n.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/index/j-s-o-n.php?branch=main&color=%234f5d95&label=j-s-o-n.php&labelColor=%231f2328&style=flat-square
[p-h-p.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/index/p-h-p.php?branch=main&color=%234f5d95&label=p-h-p.php&labelColor=%231f2328&style=flat-square
[x-m-l.php]: https://img.shields.io/github/size/taufik-nurrohman/minify/index/x-m-l.php?branch=main&color=%234f5d95&label=x-m-l.php&labelColor=%231f2328&style=flat-square

Motivation
----------

This project was started as a [gist][gist/minify], which now has more stars than [the copy of it][mecha-cms/x.minify],
that was actually made to abandon the gist. It was inspired by [a code snippet][ideone] that will probably get lost in
the future, so I decided to make a copy of it and put it [here][gist/ideone].

I once got an e-mail from someone who wanted to use some parts of [my Mecha CMS’ extension][mecha-cms/x.minify], for him
to use in a proprietary application, so he hoped not to be bound by [the GPL restrictions][article/gpl].

etc etc ...

[article/gpl]: https://mecha-cms.com/article/general-public-license
[gist/ideone]: https://gist.github.com/taufik-nurrohman/db723da29e69065a1130
[gist/minify]: https://gist.github.com/taufik-nurrohman/d7b310dea3b33e4732c0/804ae266c30664e7dcdf1d7d544628f7790bdad8
[ideone]: https://ideone.com/Q5USEF
[mecha-cms/x.minify]: https://github.com/mecha-cms/x.minify

Usage
-----

This converter can be installed using [Composer](https://packagist.org/packages/taufik-nurrohman/minify), but it doesn’t
need any other dependencies and just uses Composer’s ability to automatically include files. Those of you who don’t use
Composer should be able to include the `index.php` file directly into your application without any problems.

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

Require the `index.php` file in your application:

~~~ php
<?php

use function x\minify\c_s_s as minify_css;
use function x\minify\h_t_m_l as minify_html;
use function x\minify\j_s as minify_js;
use function x\minify\j_s_o_n as minify_json;
use function x\minify\p_h_p as minify_php;
use function x\minify\x_m_l as minify_xml;

require 'index.php';

echo minify_css('asdf { asdf: 0px; } asdf { /* asdf */ }'); // Returns `'asdf{asdf:0}'`
~~~

Notes
-----

This project focuses only on removing white-spaces. Other improvisations were considered as a bonus, as they were safe
to modify the source code. It can’t read your code and only perform generic tokenization, like grouping comments and
strings as a single token. It won’t fix your code, like adding an optional semi-colon at the end of a line because it
was followed by a line-break, so the semi-colon was optional initially, but then your code will be broken after the
minification because the required line-break is now gone:

~~~ js
// Before
a()
b()
c()

// After
a()b()c()
~~~

I still don’t quite understand why this way of writing JavaScript has become so popular these days. Someone who started
[this standard][standard/standard] is probably into [Python][python], but he doesn’t want to admit it.

[python]: https://github.com/python
[standard/standard]: https://github.com/standard/standard

Options
-------

_TODO_

Tests
-----

Clone this repository into the root of your web server that supports PHP and then you can open the `test.php` file with
your browser to see the result and the performance of this converter in various cases.

Tweaks
------

_TODO_

License
-------

This library is licensed under the [MIT License](LICENSE). Please consider
[donating 💰](https://github.com/sponsors/taufik-nurrohman) if you benefit financially from this library.