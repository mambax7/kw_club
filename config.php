<?php

require_once __DIR__ . '/header.php';
if (!$_SESSION['isclubAdmin']) {
    redirect_header('index.php', 3, _MD_KWCLUB_FORBBIDEN);
}
$GLOBALS['xoopsOption']['template_main'] = 'kw_club_config.tpl';
require_once XOOPS_ROOT_PATH . '/header.php';

/*-----------執行動作判斷區----------*/
require_once $GLOBALS['xoops']->path('/modules/system/include/functions.php');
$op          = system_CleanVars($_REQUEST, 'op', '', 'string');
$type        = system_CleanVars($_REQUEST, 'type', '', 'string');
$club_id     = system_CleanVars($_REQUEST, 'club_id', '', 'int');
$cate_id     = system_CleanVars($_REQUEST, 'cate_id', '', 'int');
$place_id    = system_CleanVars($_REQUEST, 'place_id', '', 'int');
$teacher_id  = system_CleanVars($_REQUEST, 'teacher_id', '', 'int');
$users_uid   = system_CleanVars($_REQUEST, 'users_uid', '', 'string');
$club_enable = system_CleanVars($_REQUEST, 'club_enable', '', 'int');

switch ($op) {
    case 'insert_kw_club_info':
        $club_id = insert_kw_club_info($type);
        header("location: {$_SERVER['PHP_SELF']}?club_id=$club_id");
        exit;

    //更新資料
    case 'update_kw_club_info':
        update_kw_club_info($club_id);
        header("location: {$_SERVER['PHP_SELF']}?club_id=$club_id");
        exit;

    //期別表單
    case 'kw_club_info_form':
        kw_club_info_form($club_id);
        break;
    //刪除期別
    case 'delete_kw_club_info':
        delete_kw_club_info($club_id);
        header("location: {$_SERVER['PHP_SELF']}");
        break;
    //複製期別
    // case "copy_kw_club_info":
    //     copy_kw_club_info($club_id);
    //     header("location: {$_SERVER['PHP_SELF']}");
    //     break;

    // case "save_club_teacher":
    //     save_club_teacher($users_uid);
    //     header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab2");
    //     exit;

    //新增資料
    case 'insert_cate':
        insert_cate($type);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab3");
        exit;

    case 'insert_place':
        insert_cate($type);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab4");
        exit;
    //新增教師
    case 'insert_teacher':
        insert_cate($type);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab2");
        exit;

    //更新資料
    case 'update_cate':
        update_cate($type, $cate_id);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab3");
        exit;

    case 'update_place':
        update_cate($type, $place_id);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab4");
        exit;

    case 'update_teacher':
        update_cate($type, $teacher_id);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab2");
        exit;

    case 'delete_cate':
        delete_cate($type, $cate_id);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab3");
        exit;

    case 'delete_place':
        delete_cate($type, $place_id);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab4");
        exit;

    case 'delete_teacher':
        delete_cate($type, $teacher_id);
        header("location: {$_SERVER['PHP_SELF']}?type=$type#setupTab2");
        exit;

    case 'update_enable':
        update_enable($club_id, $club_enable);
        header("location: {$_SERVER['PHP_SELF']}");
        exit;

    default:
        kw_club_info_list();
        // get_club_teacher();
        cate_list('teacher');
        cate_form('teacher', $teacher_id);
        cate_list('cate');
        cate_form('cate', $cate_id);
        cate_list('place');
        cate_form('place', $place_id);
        $op = 'kw_club_config';
        break;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('op', $op);
$xoopsTpl->assign('toolbar', toolbar_bootstrap($interface_menu));
$xoTheme->addStylesheet(XOOPS_URL . '/modules/kw_club/assets/css/module.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/vtable.css');
require_once XOOPS_ROOT_PATH . '/footer.php';

//列出所有kw_club_info資料
function kw_club_info_list()
{
    global $xoopsDB, $xoopsTpl;

    $myts = MyTextSanitizer::getInstance();

    $sql = 'select * from `' . $xoopsDB->prefix('kw_club_info') . '` order by club_year desc';

    //getPageBar($原sql語法, 每頁顯示幾筆資料, 最多顯示幾個頁數選項);
    $PageBar = getPageBar($sql, 20, 10);
    $bar     = $PageBar['bar'];
    $sql     = $PageBar['sql'];
    $total   = $PageBar['total'];

    $result = $xoopsDB->query($sql) or web_error($sql);

    $all_kw_club_info = [];
    $i                = 0;
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        $all_kw_club_info[$i] = $all;
        //以下會產生這些變數： $club_id, $club_year, $club_start_date, $club_end_date, $club_isfree, $club_backup_num, $club_uid, $club_datetime, $club_enable
        foreach ($all as $k => $v) {
            $$k = $v;
        }

        //將 uid 編號轉換成使用者姓名（或帳號）
        $uid_name = \XoopsUser::getUnameFromId($club_uid, 1);
        if (empty($uid_name)) {
            $uid_name = \XoopsUser::getUnameFromId($club_uid, 0);
        }

        //將是/否選項轉換為圖示
        $club_enable_pic = (1 == $club_enable) ? '<img src="' . XOOPS_URL . '/modules/kw_club/assets/images/yes.gif" alt="' . _MD_KWCLUB_ENABLE_1 . '" title="' . _MD_KWCLUB_ENABLE_1 . '">' : '<img src="'
                                                                                                                                                                                      . XOOPS_URL
                                                                                                                                                                                      . '/modules/kw_club/assets/images/no.gif" alt="'
                                                                                                                                                                                      . _MD_KWCLUB_ENABLE_0
                                                                                                                                                                                      . '" title="'
                                                                                                                                                                                      . _MD_KWCLUB_ENABLE_0
                                                                                                                                                                                      . '">';

        //將是/否選項轉換為圖示
        $club_isfree_text = 1 == $club_isfree ? _MD_KWCLUB_FREE_APPLY : _MD_KWCLUB_LOGIN_APPLY;

        //過濾讀出的變數值
        $club_year       = $myts->htmlSpecialChars($club_year);
        $club_start_date = $myts->htmlSpecialChars($club_start_date);
        $club_end_date   = $myts->htmlSpecialChars($club_end_date);
        $club_backup_num = $myts->htmlSpecialChars($club_backup_num);

        $all_kw_club_info[$i]['club_year'] = $club_year;
        // $all_kw_club_info[$i]['club_year_text']   = club_year_text($club_year);
        $all_kw_club_info[$i]['club_start_date']  = $club_start_date;
        $all_kw_club_info[$i]['club_end_date']    = $club_end_date;
        $all_kw_club_info[$i]['club_isfree_text'] = $club_isfree_text;
        $all_kw_club_info[$i]['club_backup_num']  = $club_backup_num;
        $all_kw_club_info[$i]['club_uid']         = $club_uid;
        $all_kw_club_info[$i]['club_uid_name']    = $uid_name;
        $all_kw_club_info[$i]['club_datetime']    = $club_datetime;
        $all_kw_club_info[$i]['club_enable']      = $club_enable;
        $all_kw_club_info[$i]['club_enable_pic']  = $club_enable_pic;
        $i++;
    }

    //刪除確認的JS
    if (!file_exists(XOOPS_ROOT_PATH . '/modules/tadtools/sweet_alert.php')) {
        redirect_header('index.php', 3, _MD_NEED_TADTOOLS);
    }
    require_once XOOPS_ROOT_PATH . '/modules/tadtools/sweet_alert.php';
    $sweet_alert_obj          = new sweet_alert();
    $delete_kw_club_info_func = $sweet_alert_obj->render('delete_kw_club_info_func', "{$_SERVER['PHP_SELF']}?op=delete_kw_club_info&club_id=", 'club_id');
    $xoopsTpl->assign('delete_kw_club_info_func', $delete_kw_club_info_func);

    $xoopsTpl->assign('bar', $bar);
    $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    $xoopsTpl->assign('all_kw_club_info', $all_kw_club_info);

    if (!file_exists(XOOPS_ROOT_PATH . '/modules/tadtools/easy_responsive_tabs.php')) {
        redirect_header('index.php', 3, _MD_NEED_TADTOOLS);
    }
    require_once XOOPS_ROOT_PATH . '/modules/tadtools/easy_responsive_tabs.php';
    $responsive_tabs = new easy_responsive_tabs('#setupTab');
    $responsive_tabs->rander();
}

//kw_club_info編輯表單
/**
 * @param string $club_id
 */
function kw_club_info_form($club_id = '')
{
    global $xoopsDB, $xoopsTpl, $xoopsUser, $semester_name_arr;

    //抓取預設值
    if (!empty($club_id)) {
        $DBV = get_kw_club_info($club_id);
    } else {
        $DBV = [];
    }

    //預設值設定

    //設定 club_id 欄位的預設值
    $club_id = !isset($DBV['club_id']) ? $club_id : $DBV['club_id'];
    $xoopsTpl->assign('club_id', $club_id);

    //設定 club_year 欄位的預設值
    $club_year = !isset($DBV['club_year']) ? '' : $DBV['club_year'];
    $xoopsTpl->assign('club_year', $club_year);
    // $xoopsTpl->assign('club_year_text', club_year_text($club_year));

    //設定 club_start_date 欄位的預設值
    $club_start_date = !isset($DBV['club_start_date']) ? date('Y-m-d 08:00') : $DBV['club_start_date'];
    $xoopsTpl->assign('club_start_date', $club_start_date);

    //設定 club_end_date 欄位的預設值
    $club_end_date = !isset($DBV['club_end_date']) ? date('Y-m-d 17:30') : $DBV['club_end_date'];
    $xoopsTpl->assign('club_end_date', $club_end_date);

    //設定 club_isfree 欄位的預設值
    $club_isfree = !isset($DBV['club_isfree']) ? 0 : $DBV['club_isfree'];
    $xoopsTpl->assign('club_isfree', $club_isfree);

    //設定 club_backup_num 欄位的預設值
    $club_backup_num = !isset($DBV['club_backup_num']) ? '' : $DBV['club_backup_num'];
    $xoopsTpl->assign('club_backup_num', $club_backup_num);

    //設定 club_uid 欄位的預設值
    $user_uid = $xoopsUser ? $xoopsUser->uid() : '';
    $club_uid = !isset($DBV['club_uid']) ? $user_uid : $DBV['club_uid'];
    $xoopsTpl->assign('club_uid', $club_uid);

    //設定 club_datetime 欄位的預設值
    $club_datetime = !isset($DBV['club_datetime']) ? date('Y-m-d H:i:s') : $DBV['club_datetime'];
    $xoopsTpl->assign('club_datetime', $club_datetime);

    //設定 club_enable 欄位的預設值
    $club_enable = !isset($DBV['club_enable']) ? 1 : $DBV['club_enable'];
    $xoopsTpl->assign('club_enable', $club_enable);

    $op = empty($club_id) ? 'insert_kw_club_info' : 'update_kw_club_info';

    //套用formValidator驗證機制
    if (!file_exists(TADTOOLS_PATH . '/formValidator.php')) {
        redirect_header('index.php', 3, _TAD_NEED_TADTOOLS);
    }
    require_once TADTOOLS_PATH . '/formValidator.php';
    $formValidator = new formValidator('#myForm', true);
    $formValidator->render();

    //加入Token安全機制
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
    $token      = new \XoopsFormHiddenToken();
    $token_form = $token->render();
    $xoopsTpl->assign('type', $_REQUEST['type']);
    $xoopsTpl->assign('token_form', $token_form);
    $xoopsTpl->assign('action', $_SERVER['PHP_SELF']);
    $xoopsTpl->assign('next_op', $op);
    $xoopsTpl->assign('arr_year', get_all_year());
    //引入日期
    require_once XOOPS_ROOT_PATH . '/modules/tadtools/cal.php';
    $cal = new My97DatePicker();
    $cal::render();

    // $arr_num = array();
    // for ($i = 0; $i <= 10; $i++) {
    //     $arr_num[$i] = $i;
    // }
    // $xoopsTpl->assign('arr_num', $arr_num);

    // $arr_semester = get_semester();
    // $xoopsTpl->assign('arr_semester', $arr_semester);
}

//新增資料到kw_club_info中
/**
 * @param string $type
 * @return int
 */
function insert_kw_club_info($type = '')
{
    global $xoopsDB, $xoopsUser;

    //XOOPS表單安全檢查
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $error = implode('<br>', $GLOBALS['xoopsSecurity']->getErrors());
        redirect_header($_SERVER['PHP_SELF'], 3, $error);
    }

    $myts = MyTextSanitizer::getInstance();

    $club_year       = $myts->addSlashes($_POST['club_year']);
    $club_start_date = $myts->addSlashes($_POST['club_start_date']);
    $club_end_date   = $myts->addSlashes($_POST['club_end_date']);
    $club_isfree     = (int)$_POST['club_isfree'];
    $club_backup_num = (int)$_POST['club_backup_num'];
    $uid             = $xoopsUser ? $xoopsUser->uid() : '';
    $club_uid        = !empty($_POST['club_uid']) ? (int)$_POST['club_uid'] : $uid;
    $club_datetime   = date('Y-m-d H:i:s', xoops_getUserTimestamp(time()));
    $club_enable     = (int)$_POST['club_enable'];

    $sql = 'insert into `' . $xoopsDB->prefix('kw_club_info') . "` (
        `club_year`,
        `club_start_date`,
        `club_end_date`,
        `club_isfree`,
        `club_backup_num`,
        `club_uid`,
        `club_datetime`,
        `club_enable`
    ) values(
        '{$club_year}',
        '{$club_start_date}',
        '{$club_end_date}',
        '{$club_isfree}',
        '{$club_backup_num}',
        '{$club_uid}',
        '{$club_datetime}',
        '{$club_enable}'
    )";
    $xoopsDB->query($sql) or web_error($sql);

    //取得最後新增資料的流水編號
    $club_id = $xoopsDB->getInsertId();

    //設定相關變數
    $_SESSION['club_year']          = $club_year;
    $_SESSION['club_start_date']    = $club_start_date;
    $_SESSION['club_start_date_ts'] = strtotime($club_start_date);
    $_SESSION['club_end_date']      = $club_end_date;
    $_SESSION['club_end_date_ts']   = strtotime($club_end_date);
    $_SESSION['club_isfree']        = $club_isfree;
    $_SESSION['club_backup_num']    = $club_backup_num;

    //複製期別的所有社團
    $copy_year = $myts->addSlashes($_POST['copy_year']);

    if ('copy' === $type && !empty($club_year) && !empty($copy_year)) {
        kw_club_info_copy($club_year, $copy_year);
    }

    return $club_id;
}

//複製kw_club_info整的期別和社團內容
/**
 * @param string $club_year
 * @param string $copy_year
 * @return mixed
 */
function kw_club_info_copy($club_year = '', $copy_year = '')
{
    global $xoopsDB, $xoopsUser;

    if (empty($club_year) || empty($copy_year)) {
        redirect_header('index.php', 3, _TAD_NEED_TADTOOLS);
    }

    $uid   = $xoopsUser->uid();
    $today = date('Y-m-d H:i:s');
    $ip    = get_ip();

    //檢查kw_club_class where club_year = $copy_year;
    $sql = 'select * from `' . $xoopsDB->prefix('kw_club_class') . "` where `club_year`='$copy_year' order by `class_id` ";
    $result = $xoopsDB->query($sql) or web_error($sql);
    $arr = [];
    while (false !== ($arr = $xoopsDB->fetchArray($result))) {
        $sql_copy = 'insert into `' . $xoopsDB->prefix('kw_club_class') . "` (
        `club_year`,
        `class_num`,
        `cate_id`,
        `class_title`,
        `teacher_id`,
        `class_week`,
        `class_grade`,
        `class_date_open`,
        `class_date_close`,
        `class_time_start`,
        `class_time_end`,
        `place_id`,
        `class_member`,
        `class_money`,
        `class_fee`,
        `class_note`,
        `class_isopen`,
        `class_ischecked`,
        `class_desc`,
        `class_uid`,
        `class_datetime`,
        `class_ip`
    ) values(
        '{$club_year}',
        '{$arr['class_num']}',
        '{$arr['cate_id']}',
        '{$arr['class_title']}',
        '{$arr['teacher_id']}',
        '{$arr['class_week']}',
        '{$arr['class_grade']}',
        '{$arr['class_date_open']}',
        '{$arr['class_date_close']}',
        '{$arr['class_time_start']}',
        '{$arr['class_time_end']}',
        '{$arr['place_id']}',
        '{$arr['class_member']}',
        '{$arr['class_money']}',
        '{$arr['class_fee']}',
        '{$arr['class_note']}',
        '{$arr['class_isopen']}',
        '',
        '{$arr['class_desc']}',
        '{$uid}',
        '{$today}',
        '{$ip}'
    )";
        $xoopsDB->query($sql_copy) or web_error($sql_copy);
    }

    return $arr_year;
    //複製kw_club_class 設定club_year = $club_year
}

//更新kw_club_info某一筆資料
/**
 * @param string $club_id
 * @return int|string
 */
function update_kw_club_info($club_id = '')
{
    global $xoopsDB, $xoopsUser;

    //XOOPS表單安全檢查
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $error = implode('<br>', $GLOBALS['xoopsSecurity']->getErrors());
        redirect_header($_SERVER['PHP_SELF'], 3, $error);
    }

    $myts = MyTextSanitizer::getInstance();

    $club_id         = (int)$_POST['club_id'];
    $club_year       = $myts->addSlashes($_POST['club_year']);
    $club_start_date = $myts->addSlashes($_POST['club_start_date']);
    $club_end_date   = $myts->addSlashes($_POST['club_end_date']);
    $club_isfree     = (int)$_POST['club_isfree'];
    $club_backup_num = (int)$_POST['club_backup_num'];
    $uid             = $xoopsUser ? $xoopsUser->uid() : '';
    $club_uid        = !empty($_POST['club_uid']) ? (int)$_POST['club_uid'] : $uid;
    $club_datetime   = date('Y-m-d H:i:s', xoops_getUserTimestamp(time()));
    $club_enable     = (int)$_POST['club_enable'];

    $sql = 'update `' . $xoopsDB->prefix('kw_club_info') . "` set
    `club_year` = '{$club_year}',
    `club_start_date` = '{$club_start_date}',
    `club_end_date` = '{$club_end_date}',
    `club_isfree` = '{$club_isfree}',
    `club_backup_num` = '{$club_backup_num}',
    `club_uid` = '{$club_uid}',
    `club_datetime` = '{$club_datetime}',
    `club_enable` = '{$club_enable}'
    where `club_id` = '$club_id'";
    $xoopsDB->queryF($sql) or web_error($sql);

    //update session
    $_SESSION['club_year']          = $club_year;
    $_SESSION['club_start_date']    = $club_start_date;
    $_SESSION['club_start_date_ts'] = strtotime($club_start_date);
    $_SESSION['club_end_date']      = $club_end_date;
    $_SESSION['club_end_date_ts']   = strtotime($club_end_date);
    $_SESSION['club_isfree']        = $club_isfree;
    $_SESSION['club_backup_num']    = $club_backup_num;

    return $club_id;
}

//刪除kw_club_info某筆資料資料
/**
 * @param string $club_id
 */
function delete_kw_club_info($club_id = '')
{
    global $xoopsDB;

    if (empty($club_id)) {
        return;
    }
    $arr_club = get_kw_club_info($club_id);

    //刪除期別社團
    $sql = 'delete from `' . $xoopsDB->prefix('kw_club_class') . "`
    where `club_year` = '{$arr_club['club_year']}'";
    $xoopsDB->queryF($sql) or web_error($sql);

    //刪除期別
    $sql = 'delete from `' . $xoopsDB->prefix('kw_club_info') . "`
    where `club_id` = '{$club_id}'";
    $xoopsDB->queryF($sql) or web_error($sql);
}

//以流水號取得某筆kw_club_info資料
/**
 * @param string $club_id
 * @return array|false|void
 */
function get_kw_club_info($club_id = '')
{
    global $xoopsDB;

    if (empty($club_id)) {
        return;
    }

    $sql = 'select * from `' . $xoopsDB->prefix('kw_club_info') . "`
    where `club_id` = '{$club_id}'";
    $result = $xoopsDB->query($sql) or web_error($sql);
    $data = $xoopsDB->fetchArray($result);

    return $data;
}

//kw_club_cate編輯表單
/**
 * @param        $type
 * @param string $cate_id
 */
function cate_form($type, $cate_id = '')
{
    global $xoopsDB, $xoopsTpl, $xoopsUser;

    //抓取預設值
    $DBV = empty($cate_id) ? [] : get_cate($cate_id, $type);

    //預設值設定
    $xoopsTpl->assign('type', $type);

    //設定 cate_id 欄位的預設值
    $id = !isset($DBV[$type . '_id']) ? $cate_id : $DBV[$type . '_id'];
    $xoopsTpl->assign($type . '_id', $id);

    //設定 title 欄位的預設值
    $title = !isset($DBV[$type . '_title']) ? '' : $DBV[$type . '_title'];
    $xoopsTpl->assign($type . '_title', $title);

    //設定 desc 欄位的預設值
    $desc = !isset($DBV[$type . '_desc']) ? '' : $DBV[$type . '_desc'];
    $xoopsTpl->assign($type . '_desc', $desc);

    //設定 sort 欄位的預設值
    $sort = !isset($DBV[$type . '_sort']) ? kw_club_max_sort($type) : $DBV[$type . '_sort'];
    $xoopsTpl->assign($type . '_sort', $sort);

    //設定 enable 欄位的預設值
    $enable = !isset($DBV[$type . '_enable']) ? '' : $DBV[$type . '_enable'];
    $xoopsTpl->assign($type . '_enable', $enable);

    $op = empty($cate_id) ? "insert_{$type}" : "update_{$type}";
    $xoopsTpl->assign($type . '_op', $op);

    //加入Token安全機制
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
    $token      = new \XoopsFormHiddenToken();
    $token_form = $token->render();
    $xoopsTpl->assign($type . '_token', $token_form);

    //套用formValidator驗證機制
    if (!file_exists(TADTOOLS_PATH . '/formValidator.php')) {
        redirect_header('index.php', 3, _TAD_NEED_TADTOOLS);
    }
    require_once TADTOOLS_PATH . '/formValidator.php';
    $formValidator = new formValidator('.myForm', true);
    $formValidator->render();
}

//新增資料到kw_club_cate中
/**
 * @param $type
 * @return int
 */
function insert_cate($type)
{
    global $xoopsDB, $xoopsTpl;

    //XOOPS表單安全檢查
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $error = implode('<br>', $GLOBALS['xoopsSecurity']->getErrors());
        redirect_header($_SERVER['PHP_SELF'], 3, $error);
    }

    $myts = MyTextSanitizer::getInstance();

    $title  = $myts->addSlashes($_POST[$type . '_title']);
    $desc   = $myts->addSlashes($_POST[$type . '_desc']);
    $sort   = (int)$_POST[$type . '_sort'];
    $enable = (int)$_POST[$type . '_enable'];

    $sql = 'insert into `' . $xoopsDB->prefix('kw_club_' . $type) . "` (
        `{$type}_title`,
        `{$type}_desc`,
        `{$type}_sort`,
        `{$type}_enable`
        ) values(
        '{$title}',
        '{$desc}',
        '{$sort}',
        '{$enable}'
    )";
    $xoopsDB->query($sql) or web_error($sql);

    //取得最後新增資料的流水編號
    $id = $xoopsDB->getInsertId();

    return $id;
}

//更新kw_club_cate某一筆資料
/**
 * @param        $type
 * @param string $cate_id
 * @return string
 */
function update_cate($type, $cate_id = '')
{
    global $xoopsDB, $xoopsTpl;

    //XOOPS表單安全檢查
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $error = implode('<br>', $GLOBALS['xoopsSecurity']->getErrors());
        redirect_header($_SERVER['PHP_SELF'], 3, $error);
    }

    $myts = MyTextSanitizer::getInstance();

    $title  = $myts->addSlashes($_POST[$type . '_title']);
    $desc   = $myts->addSlashes($_POST[$type . '_desc']);
    $sort   = (int)$_POST[$type . '_sort'];
    $enable = (int)$_POST[$type . '_enable'];

    $sql = 'update `' . $xoopsDB->prefix('kw_club_' . $type) . "` set
        `{$type}_title` = '{$title}',
        `{$type}_desc` = '{$desc}',
        `{$type}_sort` = '{$sort}',
        `{$type}_enable` = '{$enable}'
    where `{$type}_id` = '{$cate_id}'";
    $xoopsDB->queryF($sql) or web_error($sql);

    return $cate_id;
}

//刪除kw_club_cate某筆資料資料
/**
 * @param        $type
 * @param string $cate_id
 */
function delete_cate($type, $cate_id = '')
{
    global $xoopsDB;

    if (empty($cate_id)) {
        redirect_header('config.php', 3, _MD_KWCLUB_NEED_CATE_ID);
    }

    $sql = 'delete from `' . $xoopsDB->prefix('kw_club_' . $type) . '`
    where `' . $type . "_id` = '{$cate_id}'";
    $xoopsDB->queryF($sql) or web_error($sql);
}

//自動取得kw_club_cate的最新排序
/**
 * @param $type
 * @return mixed
 */
function kw_club_max_sort($type)
{
    global $xoopsDB;
    $sql = "select max(`{$type}_sort`) from `" . $xoopsDB->prefix('kw_club_' . $type) . '`';
    $result = $xoopsDB->query($sql) or web_error($sql);
    list($sort) = $xoopsDB->fetchRow($result);

    return ++$sort;
}

//設定社團老師
function get_club_teacher()
{
    global $xoopsTpl, $xoopsDB;
    $groupid  = group_id_from_name(_MD_KWCLUB_TEACHER_GROUP);
    $user_arr = [];
    //列出群組中有哪些人
    if ($groupid) {
        /* @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        $user_arr      = $memberHandler->getUsersByGroup($groupid);
    }

    $sql = 'select uid, uname, name from ' . $xoopsDB->prefix('users') . ' order by uname';
    $result = $xoopsDB->query($sql) or web_error($sql);

    $myts    = MyTextSanitizer::getInstance();
    $user_ok = $user_yet = '';
    while (false !== ($all = $xoopsDB->fetchArray($result))) {
        foreach ($all as $k => $v) {
            $$k = $v;
        }
        $name  = $myts->htmlSpecialChars($name);
        $uname = $myts->htmlSpecialChars($uname);
        $name  = empty($name) ? '' : " ({$name})";
        if (!empty($user_arr) and in_array($uid, $user_arr)) {
            $user_ok .= "<option value=\"$uid\">{$uid} {$name} {$uname} </option>";
        } else {
            $user_yet .= "<option value=\"$uid\">{$uid} {$name} {$uname} </option>";
        }
    }
    //加入Token安全機制
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
    $token = new \XoopsFormHiddenToken();
    $xoopsTpl->assign('teacher_token', $token->render());
    $xoopsTpl->assign('user_arr', implode(',', $user_arr));
    $xoopsTpl->assign('user_ok', $user_ok);
    $xoopsTpl->assign('user_yet', $user_yet);
}

//儲存社團老師
/**
 * @param $users_uid
 */
function save_club_teacher($users_uid)
{
    //XOOPS表單安全檢查
    if (!$GLOBALS['xoopsSecurity']->check()) {
        $error = implode('<br>', $GLOBALS['xoopsSecurity']->getErrors());
        redirect_header($_SERVER['PHP_SELF'], 3, $error);
    }

    $users   = explode(',', $users_uid);
    $groupid = group_id_from_name(_MD_KWCLUB_TEACHER_GROUP);

    //列出群組中有哪些人
    if ($groupid) {
        /* @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        $user_arr      = $memberHandler->getUsersByGroup($groupid);
        //先從群組移除
        $memberHandler->removeUsersFromGroup($groupid, $user_arr);
        //再加入群組
        if (is_array($users)) {
            /* @var \XoopsMemberHandler $memberHandler */
            $memberHandler = xoops_getHandler('member');
            foreach ($users as $uid) {
                $memberHandler->addUserToGroup($groupid, $uid);
            }
        }
    }
}

//改變啟用狀態
/**
 * @param $club_id
 * @param $club_enable
 */
function update_enable($club_id, $club_enable)
{
    global $xoopsDB;

    $sql = 'update `' . $xoopsDB->prefix('kw_club_info') . "` set
    `club_enable` = '{$club_enable}'
    where `club_id` = '$club_id'";
    $xoopsDB->queryF($sql) or web_error($sql);
}
