rts
===

requirements
------------
- php 5.4
  - with [phpredis](https://github.com/nicolasff/phpredis)
- node.js 0.8.11
  - with socket.io 0.9
  - with [redis](https://github.com/mranney/node_redis)
- redis 2.4.17

installation
------------
- install `node` via package manager or from source, with npm
- install `socket.io`, `redis` via npm
- install `phpredis`
- install `redis server`

run
---
- with `redis server` running on `localhost`, start `bin/server.js`
- navigate to [localhost/rts](http://localhost/rts/) using webkit or gecko


license
-------
This software is free. It comes without any warranty, to the extent permitted by applicable law. You can redistribute it and/or modify it under the terms of the Do What The Fuck You Want To Public License, Version 2, as published by Sam Hocevar. See [http://sam.zoy.org/wtfpl/COPYING](http://sam.zoy.org/wtfpl/COPYING) for more details.