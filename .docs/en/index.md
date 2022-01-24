# Getting started

This module adds support for managing [FastyBird](https://www.fastybird.com) IoT devices.

## Installation

### Application backend in PHP

The best way to install **fastybird/devices-module** is using [Composer](https://getcomposer.org/).

> If you don't have Composer yet, [download it](https://getcomposer.org/download/) following the instructions.

##### Create new project

If you don't have a project created yet you could start with Nette base project.

You could create new project with simple composer command.

```sh
composer create-project nette/web-project path/to/install
```

Everything required will be then installed in the provided folder.

```sh
cd path/to/install
```

##### Install module

Module could be added to your project with composer command:

```sh
composer require fastybird/devices-module
```

### Application frontend in JS

The best way to install **@fastybird/devices-module** is using [Yarn](https://yarnpkg.com/):

```sh
yarn add @fastybird/devices-module
```

or if you prefer npm:

```sh
npm install @fastybird/devices-module
```

### Application workers in Python

The best way to install **fastybird-devices-module** is using [pip](https://pip.pypa.io/):

```sh
pip install fastybird-devices-module
```

## Configuration

This module is dependent on other Nette extensions. All this extensions have to enabled and configured in NEON
configuration file.

Example configuration could be found [here](https://github.com/FastyBird/devices-module/blob/master/config/example.neon)

## Initialization

This module is using database, and need some initial data to be inserted into it.

Execution of command is dependend on you current implementation. This module is dependend
on [contribute/console](https://github.com/contributte/console) extension, so check it out to get know how to configure
your console entrypoint.

After creating console entrypoint you could call module console command:

```sh
your-console-entrypoint fb:devices-module:initialize
```

This console command is interactive and will ask for all required information.

## Running module interface

This module is dependent on [fastybird/web-server](https://github.com/FastyBird/web-server) which is server-less web
server for serving API content. This module is registering its routes to this webserver automatically. All what you have
to do is start this server with your console entrypoint:

```sh
your-console-entrypoint fb:web-server:start
```

After successful start, server is listening for incoming http api request messages from clients.

## Register Vuex ORM models

This module could be registered in your Vuex ORM instance

```js
import VuexORM, { Database } from '@vuex-orm/core'
import DevicesModule from '@fastybird/devices-module'

// Create new instance of Database
const database = new Database()

VuexORM.use(DevicesModule, { database })

export default {
  plugins: [
    VuexORM.install(database),
  ],
}
```

## Registering Python services

This module is using [dependency injection module](https://github.com/kodemore/kink) and has created services registration factory.
All what you have to do i create SQLAlchemy session instance and register it to DI:

```python
from kink import di

from sqlalchemy import create_engine
from sqlalchemy.orm import Session

from fastybird_devices_module.bootstrap import register_services

db_engine = create_engine(f"mysql+pymysql://username:password@127.0.0.1/database_name")
db_session = Session(db_engine)

# Inject database session into DI
# Module will autowire session in its own services
di[Session] = db_session

# Register module services into DI container
register_services()
```
