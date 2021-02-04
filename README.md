
# [PHP] SQL DATABASE USING PDO
*By Ayman Adil (Last source code update: 20/09/2020)*


## Table of contents
- [Description](#description)
- [SELECT clause](#select-clause)
- [WHERE clause](#where-clause)
- [GROUP BY clause](#group-by-clause)
- [ORDER BY clause](#order-by-clause)
- [AS (alias) keyword](#as-alias-keyword)
- [INSERT INTO clause](#insert-into-keyword)
- [UPDATE clause](#update-clause)
- [UNION/UNION ALL clauses](#unionunion-all-clauses)
- [NATURAL JOIN clause](#natural-join-clause)
- [DELETE clause](#delete-clause)
- [TRUNCATE TABLE clause](#truncate-table-clause)
- [LIMIT clause](#limit-clause)
- [PDO methods](#pdo-methods)

### Description
The purpose of this program is to facilitate the creation of SQL queries on PHP.

First create the query with <tt>select()</tt>, <tt>insert()</tt>, <tt>update()</tt>, <tt>where()</tt>, ... methods then prepare and execute it with <tt>[result()](#get-results)</tt> method.


### SELECT clause

#### Select all columns

~~~~php
// [SQL] SELECT * FROM Users
$db->select('*', 'Users');
$result = $db->result();
~~~~

#### Select a single column

~~~~php
// [SQL] SELECT first_name FROM Users
$db->select('first_name', 'Users');
$result = $db->result('fetch'); // i.e using PDOStatement::fetch
~~~~

#### Select multiple columns

~~~~php
// [SQL] SELECT first_name, last_name FROM Users
$db->select(['first_name', 'last_name'], 'Users');
$result = $db->result();
~~~~

### WHERE clause

#### AND/OR OPERATORS
~~~~php
// [SQL] SELECT * FROM Users WHERE id > 10 AND birthdate = '2012-12-12'
$db->select('*', 'Users')->where(['id >' => 10, 'birthdate =' => '2012-12-12']);
$result = $db->result();
~~~~
~~~~php
// [SQL] SELECT * FROM Users WHERE id > 10 OR birthdate = '2012-12-12'
$db->select('*', 'Users')->where(['id >' => 10, 'birthdate =' => '2012-12-12'], 'OR');
$result = $db->result();
~~~~

#### IN/NOT IN OPERATOR

~~~~php
// [SQL] SELECT * FROM Users WHERE birthdate IN ('2011-11-11', '2012-12-12')
$db->select('*', 'Users')->where(['birthdate IN' => ['2011-11-11', '2012-12-12']]);
$result = $db->result();
~~~~

#### BETWEEN/NOT BETWEEN OPERATOR

~~~~php
// [SQL] SELECT * FROM Users WHERE id BETWEEN 51 AND 100
$db->select('*', 'Users')->where(['id BETWEEN' => ['51', '100']]);
$result = $db->result();
~~~~

### GROUP BY clause

#### Group by one column

~~~~php
// [SQL] SELECT id, SUM('orderPrices') FROM 'Orders' GROUP BY id
$db->select(['id', 'SUM(orderPrices)'], 'Orders')->groupBy('id');
$result = $db->result();
~~~~

#### Group by multiple columns

~~~~php
// [SQL] SELECT first_name, last_name, SUM('orderPrices') FROM 'Orders' GROUP BY first_name, last_name
$db->select(['first_name', 'last_name', 'SUM(orderPrices)'], 'Orders')->groupBy(['first_name', 'last_name']);
$result = $db->result();
~~~~

### ORDER BY clause

#### Order by one column

~~~~php
// [SQL] SELECT id, first_name FROM 'Users' ORDER BY id DESC
$db->select(['id', 'first_name'], 'Users')->orderBy('id DESC');
$result = $db->result();
~~~~

#### Order by multiple columns

~~~~php
// [SQL] SELECT id, first_name, birthday FROM 'Users' ORDER BY id DESC, first_name
$db->select(['id', 'first_name'], 'Users')->orderBy(['id DESC', 'first_name']);
$result = $db->result();
~~~~

### AS (alias) keyword

#### Aliases on columns

~~~~php
// [SQL] SELECT id AS identity, first_name AS first_name FROM 'Users'
$db->select(['id AS identity', 'first_name AS firstName'], 'Users');
$result = $db->result();
~~~~

#### Alias on table

~~~~php
// [SQL] SELECT * FROM 'Users' AS UsersInformation
$db->select('*', 'Users AS UsersInformation');
$result = $db->result();
~~~~

### INSERT clause

#### Insert only values

~~~~php
// [SQL] INSERT INTO Users VALUES (5, 'toto', 'moto', '10-10-2010');
$db->insertInto('Users', [5, 'toto', 'moto', '10-10-2010']);
$result = $db->result();
~~~~

#### Insert values in only specified columns

~~~~php
// [SQL] INSERT INTO Users ('first_name', 'last_name') VALUES ('toto', 'moto');
$db->insertInto('Users', ['first_name', 'last_name'], ['toto', 'moto']);
$result = $db->result();
~~~~

### UPDATE clause

#### Update all rows

~~~~php
// [SQL] UPDATE 'Users' SET 'first_name' = 'toto';
$db->update('Users', ['first_name' => 'toto']);
$result = $db->result();
~~~~

#### Update a specified row

~~~~php
// [SQL] UPDATE 'Users' SET 'first_name' = 'toto' WHERE id = 10;
$db->update('Users', ['first_name' => 'toto'])->where(['id =' => 10]);
$result = $db->result();
~~~~

### UNION/UNION ALL clauses

#### UNION clause

~~~~php
// [SQL] SELECT * FROM 'Orders1' UNION SELECT * FROM 'Orders2'
$db->select('*', 'Orders1')->unionSelect('*', 'Orders2');
$result = $db->result();
~~~~

#### UNION ALL clause

~~~~php
// [SQL] SELECT * FROM 'Orders1' UNION ALL SELECT * FROM 'Orders2'
$db->select('*', 'Orders1')->unionAllSelect('*', 'Orders2');
$result = $db->result();
~~~~

### NATURAL JOIN clause

~~~~php
// [SQL] SELECT * FROM Users NATURAL JOIN Country
$db->select('*', 'Users')->naturalJoin('Country');
$result = $db->result();
~~~~

### DELETE clause

#### Delete all rows

~~~~php
// [SQL] DELETE FROM 'Users'
$db->delete('Users');
$result = $db->result();
~~~~

#### Delete specific rows

~~~~php
// [SQL] DELETE FROM 'Users' WHERE id = 10
$db->delete('Users')->where(['id =' => 10]);
$result = $db->result();
~~~~

### TRUNCATE TABLE clause

~~~~php
// [SQL] TRUNCATE TABLE 'Users'
$db->truncate('Users');
$result = $db->result();
~~~~

### LIMIT clause

~~~~php
// [SQL] SELECT * FROM Users LIMIT 2
$db->select('*', 'Users')->limit(2);
$result = $db->result();
~~~~

~~~~php
// [SQL] SELECT * FROM Users LIMIT 3, 2
$db->select('*', 'Users')->limit(3, 2);
$result = $db->result();
~~~~

### Get results

To prepare and execute the query that you had build, you have to use <tt>result()</tt> method.

If you want to fetch data, you have to specify the fetch method you want to use (by default, it is <tt>PDOStatement::fetchAll</tt>).

For example, if you want to use <tt>PDOStatement::fetchColumn</tt>, <tt>result()</tt> argument must be <tt>'fetchColumn'</tt>. See [another example](#select-a-single-column).

### PDO methods

You can also use any PDO methods.