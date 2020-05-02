<?php

define('ACCESS_PUBLIC', 'public');
define('ACCESS_PRIVATE', 'private');
define('ACCESS_MODERATOR', 'moderator');
define('ACCESS_DEFECTIVE', 'defective');

$routes = [
    '\App\Controllers\AuthorController' => [
        'prefix' => '/author',
        'resources' => [
            /**
            * @url find
            * @method POST
            * @access public
            *
            * @params query string
            * @params genre_id int
            *
            * @params page int
            * @params page_size int
            *
            **/
            [
                'type' => 'post',
                'path' => '/find',
                'action' => 'getAuthorsAction',
                'access' => 'public',
            ],

        ]
    ],
    '\App\Controllers\BookController' => [
        'prefix' => '/book',
        'resources' => [
            /**
            * @url find
            * @method POST
            * @access public
            *
            * @params author_id int
            * @params genre_id int
            * @params query string
            *
            * @params page int
            * @params page_size int
            *
            **/
            [
                'type' => 'post',
                'path' => '/find',
                'action' => 'getBooksAction',
                'access' => 'public',
            ],

            /**
            * @url recommends
            * @method POST
            * @access private
            *
            * @params genre_id int
            * @params query string = ''
            *
            * @params! list_id int
            *
            * @params page int = 1 >= 1
            * @params page_size int = 10
            *
            **/
            [
                'type' => 'post',
                'path' => '/recommends',
                'action' => 'getRecommendsAction',
                'access' => 'private',
            ],

            /**
            * @url /form-recommends
            * @method POST
            * @access private
            *
            * @params! list_id
            **/
            [
                'type' => 'post',
                'path' => '/form-recommends',
                'action' => 'formNewRecommendationListAction',
                'access' => 'private',
            ],

            /**
            * @url get/info
            *
            * @method GET
            * @access public
            *
            * @params! book_id int
            *
            **/
            [
                'type' => 'get',
                'path' => '/get/info',
                'action' => 'getBookInfoAction',
                'access' => 'public',
            ],

            /**
            * @url download/file/txt
            *
            * @method GET
            * @access public
            *
            * @params! book_id int
            *
            **/
            [
                'type' => 'get',
                'path' => '/download/file/txt',
                'action' => 'downloadFileAction',
                'access' => 'public',
            ],

            /**
            * @url change/paths
            *
            * @method GET
            * @access public
            *
            * @params! book_id int
            *
            **/
            [
                'type' => 'get',
                'path' => '/change/paths',
                'action' => 'changePathsAction',
                'access' => 'public',
            ],

        ]
    ],
    '\App\Controllers\BookListController' => [
        'prefix' => '/book-list',
        'resources' => [
            /**
            * Добавляет список книг
            *
            * @url add
            *
            * @access private
            * @method POST
            *
            * @params !list_name string
            *
            **/
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addBookListAction',
                'access' => 'private',
            ],

            /**
            * Изменяет список книг
            *
            * @url edit
            *
            * @access private
            * @method PUT
            *
            * @params! list_id int
            * @params! list_name string
            *
            **/
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editBookListAction',
                'access' => 'private',
            ],

            /**
            * Удаляет список книг
            *
            * @url delete
            *
            * @access private
            * @method DELETE
            *
            * @params! list_id int
            *
            **/
            [
                'type' => 'delete',
                'path' => '/delete',
                'action' => 'deleteBookListAction',
                'access' => 'private',
            ],

            /**
            * возвращает списки книг
            *
            * @url get
            *
            * @access private
            * @method GET
            *
            **/
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getBookListsAction',
                'access' => 'private',
            ],

            /**
            * Добавляет книгу в список
            *
            * @url add/book
            *
            * @access private
            * @method POST
            *
            * @params !list_id int
            * @params !book_id int
            * @params !rating int >=0 <= 10
            *
            * @return array
            **/
            [
                'type' => 'post',
                'path' => '/add/book',
                'action' => 'addBookInListAction',
                'access' => 'private',
            ],

            /**
            * Удаляет книгу из списка
            *
            * @url delete/book
            *
            * @access private
            * @method DELETE
            *
            * @params !list_id int
            * @params !book_id_id int
            *
            * @return array
            **/
            [
                'type' => 'delete',
                'path' => '/delete/book',
                'action' => 'deleteBookFromListAction',
                'access' => 'private',
            ],

            /**
            * возвращает книги из списка
            *
            * @url get/books
            *
            * @access private
            * @method GET
            *
            * @params list_id
            * @params page int = 1 > 0
            * @params page_size = 10 >= 0
            **/
            [
                'type' => 'get',
                'path' => '/get/books',
                'action' => 'getBooksFromListAction',
                'access' => 'private',
            ],

            /**
            * возвращает книги из списка
            *
            * @url get/books/all
            *
            * @access private
            * @method GET
            *
            **/
            [
                'type' => 'get',
                'path' => '/get/books/all',
                'action' => 'getAllReadBooksAction',
                'access' => 'private',
            ],

        ]
    ],
    '\App\Controllers\GenreController' => [
        'prefix' => '/genre',
        'resources' => [
            /**
            * @url get
            * @method GET
            * @access public
            *
            **/
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getBooksAction',
                'access' => 'public',
            ],

        ]
    ],
    '\App\Controllers\RegisterAPIController' => [
        'prefix' => '/authentication',
        'resources' => [
            /**
            * Регистрирует пользователя в системе
            *
            * @url register
            *
            * @access public
            * @method POST
            *
            * @params login
            * @params password
            * @params activation_code
            *
            * @return array. Если все прошло успешно - [status, token, lifetime (время, после которого токен будет недействительным)],
            * иначе [status,errors => <массив сообщений об ошибках>]
            **/
            [
                'type' => 'post',
                'path' => '/register',
                'action' => 'indexAction',
                'access' => 'public',
            ],

            /**
            * Проверяет, подходит ли логин для регистрации нового пользователя
            *
            * @url /check/login
            *
            * @access public
            * @method POST
            *
            * @params login
            *
            * @return string json array Status
            **/
            [
                'type' => 'post',
                'path' => '/check/login',
                'action' => 'checkLoginAction',
                'access' => 'public',
            ],

            /**
            * Проверяет активационный код
            *
            * @url check/activation-code
            *
            * @access public
            * @method POST
            *
            * @params activation_code
            * @params login
            *
            * @return array
            **/
            [
                'type' => 'post',
                'path' => '/check/activation-code',
                'action' => 'checkActivationCodeAction',
                'access' => 'public',
            ],

            /**
            * Отправляет активационный код
            *
            * @url /get/activation-code
            *
            * @access public
            * @method POST
            *
            * @params login
            *
            * @return Response - json array в формате Status
            **/
            [
                'type' => 'post',
                'path' => '/get/activation-code',
                'action' => 'getActivationCodeAction',
                'access' => 'public',
            ],

            /**
            * Отправляет пользователю код для сброса пароля
            *
            * @url /get/resetPasswordCode
            *
            * @method POST
            * @access public
            *
            * @params login
            *
            * @return Status
            **/
            [
                'type' => 'post',
                'path' => '/get/resetPasswordCode',
                'action' => 'getResetPasswordCodeAction',
                'access' => 'public',
            ],

            /**
            * Проверяет, верен ли код для сброса пароля
            *
            * @url /check/resetPasswordCode
            *
            * @access public
            * @method POST
            *
            * @params login
            * @params reset_code
            *
            * @return Status
            **/
            [
                'type' => 'post',
                'path' => '/check/resetPasswordCode',
                'action' => 'checkResetPasswordCodeAction',
                'access' => 'public',
            ],

            /**
            * Меняет пароль, если активационный код верен
            *
            * @url /change/password
            *
            * @access public
            * @method POST
            *
            * @params login
            * @params resetcode
            * @params password
            *
            * @return string - json array Status
            **/
            [
                'type' => 'post',
                'path' => '/change/password',
                'action' => 'changePasswordAction',
                'access' => 'public',
            ],

            /**
            * Проверяет, существует ли уже никнейм
            *
            * @url /check/nickname
            *
            * @method POST
            * @access public
            *
            * @params nickname
            *
            * @return array (bool nickname_exists)
            **/
            [
                'type' => 'post',
                'path' => '/check/nickname',
                'action' => 'checkNicknameAction',
                'access' => 'public',
            ],

        ]
    ],
    '\App\Controllers\ReviewController' => [
        'prefix' => '/review',
        'resources' => [
            /**
            * Добавляет отзыв
            *
            * @url add
            *
            * @access private
            * @method POST
            *
            * @params! rating int
            * @params review_text
            * @params! book_id int
            *
            **/
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addReviewAction',
                'access' => 'private',
            ],

            /**
            * Изменяет отзыв
            *
            * @url edit
            *
            * @access private
            * @method PUT
            *
            * @params! review_id int
            * @params rating int
            * @params review_text
            *
            **/
            [
                'type' => 'put',
                'path' => '/edit',
                'action' => 'editReviewAction',
                'access' => 'private',
            ],

            /**
            * Удаляет отзыв
            *
            * @url delete
            *
            * @access private
            * @method DELETE
            *
            * @params! review_id int
            *
            **/
            [
                'type' => 'delete',
                'path' => '/delete',
                'action' => 'deleteReviewAction',
                'access' => 'private',
            ],

            /**
            * Возвращает отзывы пользователя
            *
            * @url get
            *
            * @access private
            * @method GET
            *
            * @params page int
            * @params page_size int
            *
            **/
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getAllReviewAction',
                'access' => 'private',
            ],

        ]
    ],
    '\App\Controllers\SessionAPIController' => [
        'prefix' => '/authentication',
        'resources' => [
            /**
            * Выдает текущую роль пользователя.
            *
            * @url /get/role
            *
            * @method GET
            * @access public
            **/
            [
                'type' => 'get',
                'path' => '/get/role',
                'action' => 'getCurrentRoleAction',
                'access' => 'public',
            ],

            /**
            * Возвращает аккаунты текущего пользователя
            *
            * @url /get/accounts
            *
            * @method GET
            * @access private
            *
            * @return array
            **/
            [
                'type' => 'get',
                'path' => '/get/accounts',
                'action' => 'getAccountsAction',
                'access' => 'private',
            ],

            /**
            * Авторизует пользователя в системе
            *
            * @url login
            *
            * @method POST
            * @access public
            *
            * @params login (это может быть его email или номер телефона)
            * @params password
            * @return array
            **/
            [
                'type' => 'post',
                'path' => '/login',
                'action' => 'indexAction',
                'access' => 'public',
            ],

            /**
            * Авторизация через соц. сеть.
            * Используя метод GET можно получить ссылку, после перехода по которой пользователь должен получить code
            * для дальнейшей авторизации через соц. сеть.
            * Непосредственно авторизация проходит через метод POST
            *
            * @url social : GET
            * @url /social/{social_net} : GET
            * @url /social : POST
            *
            * @access public
            * @method POST
            *
            * @param $social_net = null
            *
            * @params code
            * @params provider
            *
            * @params city_id
            * @params male
            * @params first_name
            * @params last_name
            *
            * @return array - json array в формате Status
            **/
            [
                'type' => 'get',
                'path' => '/social',
                'action' => 'authWithSocialAction',
                'access' => 'public',
            ],

            [
                'type' => 'get',
                'path' => '/social/{social_net}',
                'action' => 'authWithSocialAction',
                'access' => 'public',
            ],

            [
                'type' => 'post',
                'path' => '/social',
                'action' => 'authWithSocialAction',
                'access' => 'public',
            ],

        ]
    ],
];

return $routes;