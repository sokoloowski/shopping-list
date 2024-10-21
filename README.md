# Shopping list

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=PHP)](https://www.php.net/releases/8.2/en.php)
[![Symfony](https://img.shields.io/badge/Symfony-7.1-000000?logo=Symfony)](https://symfony.com/doc/7.1/index.html)
[![phpstan](https://github.com/sokoloowski/shopping-list/actions/workflows/phpstan.yml/badge.svg)](https://github.com/sokoloowski/shopping-list/actions/workflows/phpstan.yml)
[![phpunit](https://github.com/sokoloowski/shopping-list/actions/workflows/phpunit.yml/badge.svg)](https://github.com/sokoloowski/shopping-list/actions/workflows/phpunit.yml)
[![codecov](https://codecov.io/github/sokoloowski/shopping-list/graph/badge.svg?token=AZJ05APH80)](https://codecov.io/github/sokoloowski/shopping-list)

## Task description

Create an application designed for managing shopping lists. The application is accessible only to logged-in users. The
login and registration process is handled by the user independently. After logging in, users can define shopping lists.
Each list is assigned a suggested purchase date. Items are added to the list in the following way: either by entering
items for purchase (any item or selected from a list of the most popular items, such as bread, milk, water, cheese,
etc.) along with the quantity and specifying the weight (chosen from a list of available options). It is possible to add
a photo as a reference element. For each item, there is an option to mark the purchase as completed, which will be
visually represented as a crossed-out item on the list. Shopping lists and their current status are saved in a database.
We provide full CRUD functionality for the lists.

## Proposed technology stack

Application will be developed using the [Symfony framework](https://symfony.com/doc/current/index.html) with SQLite database. The frontend will be created without any
frameworks like React or Angular, but with the use of Twig templates.

## Suggested entities

### `User`

This entity will be provided with [Symfony's SecurityBundle](https://symfony.com/doc/current/security.html). It will
provide the basic entity (ID, username, roles and password) which can be extended with additional fields:

- e-mail address
- registration date
- verification code
- verification date
- last login date
- shopping lists (ShoppingList)

### `ShoppingList`

- ID
- name
- suggested purchase date
- owner (User)
- products (Product)

### `Product`

- ID
- name
- quantity
- reference photo
- realised (boolean)
- list (ShoppingList)

## Testing

The application should be covered with unit tests. The tests should cover the most important parts of the application,
such as the creation of shopping lists, adding products to the list, marking products as purchased, etc. The tests
should be written before the implementation of the functionality (test-driven development). PHPUnit will be used for
testing as it is well-integrated with Symfony.

## Controllers

The application should have the following controllers:

- `SecurityController` - responsible for logging in and registering users
- `OverviewController` - responsible for displaying the list/grid of shopping lists
- `ShoppingListController` - responsible for creating, updating and deleting shopping lists
- `ProductController` - responsible for adding, updating and deleting products from the list
- `SettingsController` - responsible for changing user settings

### `SecurityController`

- `/login` - displays the login form
- `/register` - displays the registration form
- `/logout` - logs the user out
- `/forgotPassword` - displays the form for resetting the password
- `/verify/{code}` - verifies the user's e-mail address
- `/changePassword/{code}` - displays the form for changing the password

### `OverviewController`

- `/` - displays the list of shopping lists

### `ShoppingListController`

- `/new` - displays the form for adding a new shopping list
- `/{id}` - displays the details of the selected shopping list
- `/{id}/edit` - displays the form for editing the selected shopping list
- `/{id}/delete` - deletes the selected shopping list

### `ProductController`

- `/{listId}/new` - displays the form for adding a new product to the list
- `/{listId}/{id}` - displays the details of the selected product
- `/{listId}/{id}/edit` - displays the form for editing the selected product
- `/{listId}/{id}/delete` - deletes the selected product
- `/{listId}/{id}/toggle` - marks the selected product as (un)purchased
- `/{listId}/{id}/photo` - displays the photo of the selected product

### `SettingsController`

- `/settings` - displays the form for changing user settings

## Additional information

This application is under heavy development. The above description is only a proposal and can be changed during the
course. The application should be developed in a modular way, so that it is easy to add new functionalities in the
future.
