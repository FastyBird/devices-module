#     Copyright 2021. FastyBird s.r.o.
#
#     Licensed under the Apache License, Version 2.0 (the "License");
#     you may not use this file except in compliance with the License.
#     You may obtain a copy of the License at
#
#         http://www.apache.org/licenses/LICENSE-2.0
#
#     Unless required by applicable law or agreed to in writing, software
#     distributed under the License is distributed on an "AS IS" BASIS,
#     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#     See the License for the specific language governing permissions and
#     limitations under the License.

# Test dependencies
import unittest
import os
import MySQLdb
from operator import attrgetter
from MySQLdb import OperationalError
from MySQLdb.cursors import Cursor
from exchange_plugin.bootstrap import create_container as exchange_plugin_create_container

# Library libs
from devices_module.bootstrap import create_container
from devices_module.models import db


class DbTestCase(unittest.TestCase):
    __db_name: str
    __database: MySQLdb.Connection
    __cursor: Cursor

    # -----------------------------------------------------------------------------

    @classmethod
    def setUpClass(cls) -> None:
        cls.__db_name = "fb_test_{}".format(os.getpid())

        cls.__setup_database()
        cls.__setup_orm()

        exchange_plugin_create_container()
        create_container({
            "database": {
                "host": "127.0.0.1",
                "username": "root",
                "password": "root",
                "database": cls.__db_name,
                "create_tables": True,
            },
        })

    # -----------------------------------------------------------------------------

    @classmethod
    def tearDownClass(cls) -> None:
        cls.__tear_down_database()

    # -----------------------------------------------------------------------------

    def setUp(self) -> None:
        self.__initialize_database_data()

    # -----------------------------------------------------------------------------

    @classmethod
    def __setup_database(cls) -> None:
        cls.__database = MySQLdb.connect(host="127.0.0.1", user="root", passwd="root")

        cls.__cursor = cls.__database.cursor()

        cls.__cursor.execute("DROP DATABASE IF EXISTS {}".format(cls.__db_name))
        cls.__cursor.execute("CREATE DATABASE {}".format(cls.__db_name))
        cls.__cursor.execute("USE {}".format(cls.__db_name))

    # -----------------------------------------------------------------------------

    @classmethod
    def __tear_down_database(cls) -> None:
        cls.__cursor.execute("DROP DATABASE IF EXISTS {}".format(cls.__db_name))

        cls.__cursor.close()
        cls.__database.close()

    # -----------------------------------------------------------------------------

    @classmethod
    def __setup_orm(cls) -> None:
        db.provider = None  # Reset database provider
        db.schema = None  # Reset generated schema

        entities = list(sorted(db.entities.values(), key=attrgetter('_id_')))

        # Check all entities...
        for entity in entities:
            table_name = entity._table_

            is_subclass = entity._root_ is not entity

            if is_subclass:
                if table_name is not None:
                    # ...and reset table name to base value
                    entity._table_ = None

    # -----------------------------------------------------------------------------

    def __initialize_database_data(self) -> None:
        query: str = """
        SET FOREIGN_KEY_CHECKS=0;
        SELECT @str := CONCAT('TRUNCATE TABLE ', table_schema, '.', table_name, ';')
            FROM information_schema.tables
            WHERE table_type = 'BASE TABLE' AND table_schema in ('{}');
        PREPARE stmt FROM @str;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        SET FOREIGN_KEY_CHECKS=1;
        """.format(self.__db_name)

        self.__cursor.execute(query)

        # self.__database.commit()

        import_file = open(os.path.join(os.path.dirname(__file__), "../sql/dummy.data.sql"), "r")
        sql_file = import_file.read()
        import_file.close()

        sql_commands = sql_file.split(";")

        # Execute every command from the input file
        for command in sql_commands:
            if command == "\n":
                continue

            # This will skip and report errors
            # For example, if the tables do not yet exist, this will skip over
            # the DROP TABLE commands
            try:
                self.__cursor.execute(command)

            except OperationalError as msg:
                print("Command skipped: {}".format(msg))

        self.__database.commit()
