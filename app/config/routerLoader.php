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
            * @params query string = ''
            *
            * @params book_ids array of int
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

            /**
            * @url add/image
            *
            * @method POST
            * @access moderator
            *
            * @params! book_id int
            * @params! image_name int
            *
            **/
            [
                'type' => 'post',
                'path' => '/add/image',
                'action' => 'setImageAction',
                'access' => 'moderator',
            ],

        ]
    ],
    '\App\Controllers\BookListController' => [
        'prefix' => '/book-list',
        'resources' => [
            /**
            * ?????????????????? ???????????? ????????
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
            * ???????????????? ???????????? ????????
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
            * ?????????????? ???????????? ????????
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
            * ???????????????????? ???????????? ????????
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
            * ?????????????????? ?????????? ?? ????????????
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
            * ?????????????? ?????????? ???? ????????????
            *
            * @url delete/book
            *
            * @access private
            * @method DELETE
            *
            * @params !list_id int
            * @params !book_id int
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
            * ???????????????????? ?????????? ???? ????????????
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
            * ???????????????????? ?????????? ???? ????????????
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
                'action' => 'getGenresAction',
                'access' => 'public',
            ],

        ]
    ],
    '\App\Controllers\PromotionController' => [
        'prefix' => '/promotion',
        'resources' => [
            /**
            * ?????????????????? ??????????
            *
            * @url add
            *
            * @access moderator
            * @method POST
            *
            * @params description string
            * @params !time_start int
            * @params !time_end int
            *
            * @params book_descriptions array => {
            *      !type => string,
            *      book_id => int
            *      author_id => int
            *      genre_id => int,
            *      query=>string
            *      !factor => float
            * }
            *
            **/
            [
                'type' => 'post',
                'path' => '/add',
                'action' => 'addPromotionAction',
                'access' => 'moderator',
            ],

            /**
            * ?????????????? ??????????
            *
            * @url delete
            *
            * @access moderator
            * @method DELETE
            *
            * @params! promotion_id int
            *
            **/
            [
                'type' => 'delete',
                'path' => '/delete',
                'action' => 'deletePromotionAction',
                'access' => 'moderator',
            ],

            /**
            * ???????????????????? ??????????
            *
            * @url get
            *
            * @access moderator
            * @method GET
            *
            * @params page int
            * @params page_size int
            *
            **/
            [
                'type' => 'get',
                'path' => '/get',
                'action' => 'getPromotionsAction',
                'access' => 'moderator',
            ],

            /**
            * ???????????????????? ??????????
            *
            * @url get/books
            *
            * @access moderator
            * @method GET
            *
            * @params !promotion_id int
            * @params page int
            * @params page_size int
            *
            **/
            [
                'type' => 'get',
                'path' => '/get/books',
                'action' => 'getBooksForPromotionAction',
                'access' => 'moderator',
            ],

            /**
            * ???????????????????? ??????????
            *
            * @url send/advertising
            *
            * @access moderator
            * @method POST
            *
            * @params !promotion_id int
            *
            **/
            [
                'type' => 'post',
                'path' => '/send/advertising',
                'action' => 'sendAdvertisingAction',
                'access' => 'moderator',
            ],

        ]
    ],
    '\App\Controllers\RegisterAPIController' => [
        'prefix' => '/authentication',
        'resources' => [
            /**
            * ???????????????????????? ???????????????????????? ?? ??????????????
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
            * @return array. ???????? ?????? ???????????? ?????????????? - [status, token, lifetime (??????????, ?????????? ???????????????? ?????????? ?????????? ????????????????????????????????)],
            * ?????????? [status,errors => <???????????? ?????????????????? ???? ??????????????>]
            **/
            [
                'type' => 'post',
                'path' => '/register',
                'action' => 'indexAction',
                'access' => 'public',
            ],

            /**
            * ??????????????????, ???????????????? ???? ?????????? ?????? ?????????????????????? ???????????? ????????????????????????
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
            * ?????????????????? ?????????????????????????? ??????
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
            * ???????????????????? ?????????????????????????? ??????
            *
            * @url /get/activation-code
            *
            * @access public
            * @method POST
            *
            * @params login
            *
            * @return Response - json array ?? ?????????????? Status
            **/
            [
                'type' => 'post',
                'path' => '/get/activation-code',
                'action' => 'getActivationCodeAction',
                'access' => 'public',
            ],

            /**
            * ???????????????????? ???????????????????????? ?????? ?????? ???????????? ????????????
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
            * ??????????????????, ?????????? ???? ?????? ?????? ???????????? ????????????
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
            * ???????????? ????????????, ???????? ?????????????????????????? ?????? ??????????
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
            * ??????????????????, ???????????????????? ???? ?????? ??????????????
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
            * ?????????????????? ??????????
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
            * ???????????????? ??????????
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
            * ?????????????? ??????????
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
            * ???????????????????? ???????????? ????????????????????????
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
            * ???????????? ?????????????? ???????? ????????????????????????.
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
            * ???????????????????? ???????????????????????? ?? ??????????????
            *
            * @url login
            *
            * @method POST
            * @access public
            *
            * @params login
            * @params password
            * @return array
            **/
            [
                'type' => 'post',
                'path' => '/login',
                'action' => 'indexAction',
                'access' => 'public',
            ],

        ]
    ],
];

return $routes;