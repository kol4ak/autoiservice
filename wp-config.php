<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе
 * установки. Необязательно использовать веб-интерфейс, можно
 * скопировать файл в "wp-config.php" и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define('DB_NAME', 'avservice');

/** Имя пользователя MySQL */
define('DB_USER', 'root');

/** Пароль к базе данных MySQL */
define('DB_PASSWORD', '');

/** Имя сервера MySQL */
define('DB_HOST', 'localhost');

/** Кодировка базы данных для создания таблиц. */
define('DB_CHARSET', 'utf8');

/** Схема сопоставления. Не меняйте, если не уверены. */
define('DB_COLLATE', '');

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'pVD4v-6@7z1&[P7aWV5xpyzLs>5]3&lp%XP)tPOP)NN@)A!fx=rF#:aQ+3Qx5>Mu');
define('SECURE_AUTH_KEY',  '+jcSqF%X5|X^<%eG9R-l3s[u_,i:(!f^w1u[kTM.#PQ@fevs21k&/wD)f9`MZg?5');
define('LOGGED_IN_KEY',    'x{L[EPu.4DO4i$=zNA4<B|PLbth)T%rDW[D+PCkJ7{Y,NwHCA0cE:*~F$/h8jC=T');
define('NONCE_KEY',        '%:5.nSeyGEyt6IN>}O|Ud&9OI)H(uNgQ-fdg+u+}/o@jKp@$7weCdN>es:K.>kPf');
define('AUTH_SALT',        'OJ[Em(wP%!kmGcM:Av4F7Z&2AJ&d/di?f?!cxb@C9i(,_OpR,_4TV0Fcb9oc7~-B');
define('SECURE_AUTH_SALT', '&N^@3[$W%*3M]nDp]SGl]}+74~ELe/)3t%c3uP3&&2~Cx-BqkUf)SS&$nI{S~sH^');
define('LOGGED_IN_SALT',   '[PBDv7L@^5HC$IQd)uHYk[08.;#Z,NN80__g&~=bVQlCMm5mRmLlyq#PScN<d2Cr');
define('NONCE_SALT',       'oAX|8SP|;-LFm%C:K&1Kov^;lQz,24zI[&03ATz8|=4|Z>0J%Nnj9HJ64|y}{1%U');

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix  = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 * 
 * Информацию о других отладочных константах можно найти в Кодексе.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Инициализирует переменные WordPress и подключает файлы. */
require_once(ABSPATH . 'wp-settings.php');
