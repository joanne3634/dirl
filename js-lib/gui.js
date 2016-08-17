// 檔名：gui.js
// 說明：管理系統頁面元件的控制

var position = 1;							// 當前選項
var total_menu_item = 13;					// 選單總共有12個物件
var item_style = "menu_item";				// 一般選項樣式名字
var item_style_selected = "menu_item_x";	// 被選取選項樣式名字
var container_name = "panel_";				// 各個頁面區塊的主要名稱
var remote_file_name = "panel_";			// 各個頁面區塊來源檔的主要名稱
var request_type = 0;						// 請求的樣式 0->讀取 1->儲存 2->更新 3->刪除
var target_type = 0;						// 訊息顯示正在處理的物件名稱, 參考 target_msg.
var db_err_type = 0;						// AJAX 呼叫發出後取得db狀態, 參考 db_error_msg
var timer = 0;								// 顯示訊息用的timer
var ajax_id = "";							// 正在處理的 id
var ajax_id_related = "";					// 同步更新的 id
var time_tag_id = "";						// 傳回到更新時間的div id
var renew_div_id = "";						// 傳回更新的div id
var renew_path = "";						// 傳到更新的檔案路徑
var renew_img_src = "";						// 傳回更新圖片路徑 (toggle 使用)
var edit_lang="tc";							// 當前編輯的語言 (p3)
var edit_doc = -1;							// 當前編輯的文件 (p9)

var request_msg = new Array();				// 記錄請求及結果的訊息
request_msg[0] = " : 讀取中, 請稍候...";
request_msg[1] = " : 儲存中, 請稍候...";
request_msg[2] = " : 更新中, 請稍候...";
request_msg[3] = " : 刪除中, 請稍候...";
request_msg[4] = " : 重新讀取中, 請稍候...";

var result_msg = new Array();
result_msg[0] = " : 讀取成功!";
result_msg[1] = " : 儲存成功!";
result_msg[2] = " : 更新成功!";
result_msg[3] = " : 刪成成功!";
result_msg[4] = " : 重新讀取成功!";

var db_err_msg = new Array();
db_err_msg[0] = "";
db_err_msg[1] = "：無法連接資料庫";
db_err_msg[2] = "：無效的資料表";
db_err_msg[3] = "：SQL執行失敗";
db_err_msg[4] = "：資料庫內部錯誤";
db_err_msg[5] = "：資料庫參數錯誤";
db_err_msg[6] = "：內部未定義的資料欄位";
db_err_msg[7] = "：SESSION參數無效";
db_err_msg[8] = "：SQL沒有產生任何效果";
db_err_msg[9] = "：請填寫所有必填欄位";



var target_msg = new Array();
target_msg[0] = " 系統資料";
target_msg[1] = " 關於我們(中文)";
target_msg[2] = " 關於我們(英文)";
target_msg[3] = " 研究領域重點(中文)";
target_msg[4] = " 研究領域重點(英文)";
target_msg[5] = " 聯絡我們(中文)";
target_msg[6] = " 聯絡我們(英文)";
target_msg[7] = " 最新消息";
target_msg[8] = " 最新消息 - 內容欄位";
target_msg[9] = " 最新消息 - 連結欄位";
target_msg[10] = " 最新消息 - 是否顯示消息";
target_msg[11] = " 最新消息 - 是否置頂";
target_msg[12] = " 實驗室成員 - 新增研究助理(快速)";
target_msg[13] = " 實驗室成員 - 新增研究助理(詳細)";
target_msg[14] = " 實驗室成員 - 新增ALUMNI(快速)";
target_msg[15] = " 實驗室成員 - 新增ALUMNI(詳細)";
target_msg[16] = " 實驗室成員 - 刪除成員";
target_msg[17] = " 實驗室成員 - 修改成員";
target_msg[18] = " 研究著作 - 新增著作";
target_msg[19] = " 研究著作 - 修改著作";
target_msg[20] = " 研究著作 - 刪除著作";
target_msg[21] = " 研究著作 - 是否顯示著作";
target_msg[22] = " 研究領域(中文)";
target_msg[23] = " 研究領域(英文)";
target_msg[24] = " 實驗室資源(中文)";
target_msg[25] = " 實驗室資源(英文)";
target_msg[26] = " 下載";
target_msg[27] = " 下載 - 是否顯示";
target_msg[28] = " 下載 - 是否置頂";
target_msg[29] = " 下載 - 修改名稱";
target_msg[30] = " 下載 - 修改連結";
target_msg[31] = " 下載 - 顯示語系";
target_msg[32] = " 設定資料庫";
target_msg[33] = " 實驗室資源";
target_msg[34] = " 實驗室資源 - 內容欄位";
target_msg[35] = " 實驗室資源 - 連結欄位";
target_msg[36] = " 實驗室資源 - 類型設定";
target_msg[37] = " 網頁使用者 - 是否啟用";
target_msg[38] = " 網頁使用者 - 使用者名稱";
target_msg[39] = " 網頁使用者 - 使用者密碼";
target_msg[40] = " 網頁使用者";
target_msg[41] = " 自訂網頁 - 修改";
target_msg[42] = " 自訂網頁 - 新增";
target_msg[43] = " 自訂網頁 - 刪除";
target_msg[44] = " 最新消息 - 建立日期";
target_msg[45] = " 名言佳句";
target_msg[46] = " 名言佳句 - 內容欄位";
target_msg[47] = " 名言佳句 - 是否顯示消息";





// 各個介面資料集的組合
var contact_group = new Array();
contact_group[0] = "p1_contact_email";
contact_group[1] = "p1_contact_tel";
contact_group[2] = "p1_contact_fax";
contact_group[3] = "p1_contact_addr";

var news_group = new Array();
news_group[0] = "p2_subject_add";
news_group[1] = "p2_link_add";
news_group[2] = "p2_visible_add";
news_group[3] = "p2_top_add";
news_group[4] = "p2_lang_add";

var slogan_group = new Array();
slogan_group[0] = "p13_subject_add";
slogan_group[1] = "p13_visible_add";

var member_group_pd_fast = new Array();
member_group_pd_fast[0] = "p4_add_pd_fast_iis_id";
member_group_pd_fast[1] = "p4_add_pd_fast_name_tc";
member_group_pd_fast[2] = "p4_add_pd_fast_name_en";
member_group_pd_fast[3] = "p4_add_pd_fast_email";
member_group_pd_fast[4] = "p4_add_pd_fast_period_in_year";
member_group_pd_fast[5] = "p4_add_pd_fast_period_in_mon";
member_group_pd_fast[6] = "p4_add_pd_fast_study_tc";
member_group_pd_fast[7] = "p4_add_pd_fast_study_en";
member_group_pd_fast[8] = "p4_add_pd_fast_research";

var member_group_pd_full = new Array();
member_group_pd_full[0] = "p4_add_pd_full_iis_id";
member_group_pd_full[1] = "p4_add_pd_full_name_tc";
member_group_pd_full[2] = "p4_add_pd_full_name_en";
member_group_pd_full[3] = "p4_add_pd_full_aka_tc";
member_group_pd_full[4] = "p4_add_pd_full_aka_en";
member_group_pd_full[5] = "p4_add_pd_full_page_link";
member_group_pd_full[6] = "p4_add_pd_full_blog_link";
member_group_pd_full[7] = "p4_add_pd_full_email";
member_group_pd_full[8] = "p4_add_pd_full_tel";
member_group_pd_full[9] = "p4_add_pd_full_cell";
member_group_pd_full[10] = "p4_add_pd_full_period_in_mon";
member_group_pd_full[11] = "p4_add_pd_full_period_in_year";
member_group_pd_full[12] = "p4_add_pd_full_study_tc";
member_group_pd_full[13] = "p4_add_pd_full_study_en";
member_group_pd_full[14] = "p4_add_pd_full_research";
member_group_pd_full[15] = "p4_add_pd_full_msn";
member_group_pd_full[16] = "p4_add_pd_full_skype";
member_group_pd_full[17] = "p4_add_pd_full_google";
member_group_pd_full[18] = "p4_add_pd_full_title_tc";
member_group_pd_full[19] = "p4_add_pd_full_title_en";
member_group_pd_full[20] = "p4_add_pd_full_birthday";
member_group_pd_full[21] = "p4_add_pd_full_page_link_en";
member_group_pd_full[22] = "p4_add_pd_full_email_stable";


var member_group_pd_full_mod = new Array();
member_group_pd_full_mod[0] = "p4_mod_pd_full_iis_id";
member_group_pd_full_mod[1] = "p4_mod_pd_full_name_tc";
member_group_pd_full_mod[2] = "p4_mod_pd_full_name_en";
member_group_pd_full_mod[3] = "p4_mod_pd_full_aka_tc";
member_group_pd_full_mod[4] = "p4_mod_pd_full_aka_en";
member_group_pd_full_mod[5] = "p4_mod_pd_full_page_link";
member_group_pd_full_mod[6] = "p4_mod_pd_full_blog_link";
member_group_pd_full_mod[7] = "p4_mod_pd_full_email";
member_group_pd_full_mod[8] = "p4_mod_pd_full_tel";
member_group_pd_full_mod[9] = "p4_mod_pd_full_cell";
member_group_pd_full_mod[10] = "p4_mod_pd_full_period_in_mon";
member_group_pd_full_mod[11] = "p4_mod_pd_full_period_in_year";
member_group_pd_full_mod[12] = "p4_mod_pd_full_study_tc";
member_group_pd_full_mod[13] = "p4_mod_pd_full_study_en";
member_group_pd_full_mod[14] = "p4_mod_pd_full_research";
member_group_pd_full_mod[15] = "p4_mod_pd_full_msn";
member_group_pd_full_mod[16] = "p4_mod_pd_full_skype";
member_group_pd_full_mod[17] = "p4_mod_pd_full_google";
member_group_pd_full_mod[18] = "p4_mod_pd_full_type";
member_group_pd_full_mod[19] = "p4_mod_pd_full_user_id";
member_group_pd_full_mod[20] = "p4_mod_pd_full_title_tc";
member_group_pd_full_mod[21] = "p4_mod_pd_full_title_en";
member_group_pd_full_mod[22] = "p4_mod_pd_full_birthday";
member_group_pd_full_mod[23] = "p4_mod_pd_full_page_link_en";
member_group_pd_full_mod[24] = "p4_mod_pd_full_email_stable";


var member_group_ra_fast = new Array();
member_group_ra_fast[0] = "p4_add_ra_fast_iis_id";
member_group_ra_fast[1] = "p4_add_ra_fast_name_tc";
member_group_ra_fast[2] = "p4_add_ra_fast_name_en";
member_group_ra_fast[3] = "p4_add_ra_fast_email";
member_group_ra_fast[4] = "p4_add_ra_fast_period_in_year";
member_group_ra_fast[5] = "p4_add_ra_fast_period_in_mon";
member_group_ra_fast[6] = "p4_add_ra_fast_study_tc";
member_group_ra_fast[7] = "p4_add_ra_fast_study_en";
member_group_ra_fast[8] = "p4_add_ra_fast_research";

var member_group_ra_full = new Array();
member_group_ra_full[0] = "p4_add_ra_full_iis_id";
member_group_ra_full[1] = "p4_add_ra_full_name_tc";
member_group_ra_full[2] = "p4_add_ra_full_name_en";
member_group_ra_full[3] = "p4_add_ra_full_aka_tc";
member_group_ra_full[4] = "p4_add_ra_full_aka_en";
member_group_ra_full[5] = "p4_add_ra_full_page_link";
member_group_ra_full[6] = "p4_add_ra_full_blog_link";
member_group_ra_full[7] = "p4_add_ra_full_email";
member_group_ra_full[8] = "p4_add_ra_full_tel";
member_group_ra_full[9] = "p4_add_ra_full_cell";
member_group_ra_full[10] = "p4_add_ra_full_period_in_mon";
member_group_ra_full[11] = "p4_add_ra_full_period_in_year";
member_group_ra_full[12] = "p4_add_ra_full_study_tc";
member_group_ra_full[13] = "p4_add_ra_full_study_en";
member_group_ra_full[14] = "p4_add_ra_full_research";
member_group_ra_full[15] = "p4_add_ra_full_msn";
member_group_ra_full[16] = "p4_add_ra_full_skype";
member_group_ra_full[17] = "p4_add_ra_full_google";
member_group_ra_full[18] = "p4_add_ra_full_title_tc";
member_group_ra_full[19] = "p4_add_ra_full_title_en";
member_group_ra_full[20] = "p4_add_ra_full_birthday";
member_group_ra_full[21] = "p4_add_ra_full_page_link_en";
member_group_ra_full[22] = "p4_add_ra_full_email_stable";


var member_group_ra_full_mod = new Array();
member_group_ra_full_mod[0] = "p4_mod_ra_full_iis_id";
member_group_ra_full_mod[1] = "p4_mod_ra_full_name_tc";
member_group_ra_full_mod[2] = "p4_mod_ra_full_name_en";
member_group_ra_full_mod[3] = "p4_mod_ra_full_aka_tc";
member_group_ra_full_mod[4] = "p4_mod_ra_full_aka_en";
member_group_ra_full_mod[5] = "p4_mod_ra_full_page_link";
member_group_ra_full_mod[6] = "p4_mod_ra_full_blog_link";
member_group_ra_full_mod[7] = "p4_mod_ra_full_email";
member_group_ra_full_mod[8] = "p4_mod_ra_full_tel";
member_group_ra_full_mod[9] = "p4_mod_ra_full_cell";
member_group_ra_full_mod[10] = "p4_mod_ra_full_period_in_mon";
member_group_ra_full_mod[11] = "p4_mod_ra_full_period_in_year";
member_group_ra_full_mod[12] = "p4_mod_ra_full_study_tc";
member_group_ra_full_mod[13] = "p4_mod_ra_full_study_en";
member_group_ra_full_mod[14] = "p4_mod_ra_full_research";
member_group_ra_full_mod[15] = "p4_mod_ra_full_msn";
member_group_ra_full_mod[16] = "p4_mod_ra_full_skype";
member_group_ra_full_mod[17] = "p4_mod_ra_full_google";
member_group_ra_full_mod[18] = "p4_mod_ra_full_type";
member_group_ra_full_mod[19] = "p4_mod_ra_full_user_id";
member_group_ra_full_mod[20] = "p4_mod_ra_full_title_tc";
member_group_ra_full_mod[21] = "p4_mod_ra_full_title_en";
member_group_ra_full_mod[22] = "p4_mod_ra_full_birthday";
member_group_ra_full_mod[23] = "p4_mod_ra_full_page_link_en";
member_group_ra_full_mod[24] = "p4_mod_ra_full_email_stable";



var member_group_al_fast = new Array();
member_group_al_fast[0] = "p4_add_al_fast_iis_id";
member_group_al_fast[1] = "p4_add_al_fast_name_tc";
member_group_al_fast[2] = "p4_add_al_fast_name_en";
member_group_al_fast[3] = "p4_add_al_fast_email";
member_group_al_fast[4] = "p4_add_al_fast_period_in_mon";
member_group_al_fast[5] = "p4_add_al_fast_period_in_year";
member_group_al_fast[6] = "p4_add_al_fast_period_out_mon";
member_group_al_fast[7] = "p4_add_al_fast_period_out_year";
member_group_al_fast[8] = "p4_add_al_fast_work_tc";
member_group_al_fast[9] = "p4_add_al_fast_work_en";

var member_group_al_full = new Array();
member_group_al_full[0] = "p4_add_al_full_iis_id";
member_group_al_full[1] = "p4_add_al_full_name_tc";
member_group_al_full[2] = "p4_add_al_full_name_en";
member_group_al_full[3] = "p4_add_al_full_aka_tc";
member_group_al_full[4] = "p4_add_al_full_aka_en";
member_group_al_full[5] = "p4_add_al_full_email";
member_group_al_full[6] = "p4_add_al_full_tel";
member_group_al_full[7] = "p4_add_al_full_cell";
member_group_al_full[8] = "p4_add_al_full_period_in_mon";
member_group_al_full[9] = "p4_add_al_full_period_in_year";
member_group_al_full[10] = "p4_add_al_full_period_out_mon";
member_group_al_full[11] = "p4_add_al_full_period_out_year";
member_group_al_full[12] = "p4_add_al_full_work_tc";
member_group_al_full[13] = "p4_add_al_full_work_en";
member_group_al_full[14] = "p4_add_al_full_research";
member_group_al_full[15] = "p4_add_al_full_birthday";
member_group_al_full[16] = "p4_add_al_full_email_stable";


var member_group_al_full_mod = new Array();
member_group_al_full_mod[0] = "p4_mod_al_full_iis_id";
member_group_al_full_mod[1] = "p4_mod_al_full_name_tc";
member_group_al_full_mod[2] = "p4_mod_al_full_name_en";
member_group_al_full_mod[3] = "p4_mod_al_full_aka_tc";
member_group_al_full_mod[4] = "p4_mod_al_full_aka_en";
member_group_al_full_mod[5] = "p4_mod_al_full_email";
member_group_al_full_mod[6] = "p4_mod_al_full_tel";
member_group_al_full_mod[7] = "p4_mod_al_full_cell";
member_group_al_full_mod[8] = "p4_mod_al_full_period_in_mon";
member_group_al_full_mod[9] = "p4_mod_al_full_period_in_year";
member_group_al_full_mod[10] = "p4_mod_al_full_period_out_mon";
member_group_al_full_mod[11] = "p4_mod_al_full_period_out_year";
member_group_al_full_mod[12] = "p4_mod_al_full_work_tc";
member_group_al_full_mod[13] = "p4_mod_al_full_work_en";
member_group_al_full_mod[14] = "p4_mod_al_full_research";
member_group_al_full_mod[15] = "p4_mod_al_full_type";
member_group_al_full_mod[16] = "p4_mod_al_full_user_id";
member_group_al_full_mod[17] = "p4_mod_al_full_birthday";
member_group_al_full_mod[18] = "p4_mod_al_full_email_stable";

var member_group_si_fast = new Array();
member_group_si_fast[0] = "p4_add_si_fast_iis_id";
member_group_si_fast[1] = "p4_add_si_fast_name_tc";
member_group_si_fast[2] = "p4_add_si_fast_name_en";
member_group_si_fast[3] = "p4_add_si_fast_email";
member_group_si_fast[4] = "p4_add_si_fast_period_in_mon";
member_group_si_fast[5] = "p4_add_si_fast_period_in_year";
member_group_si_fast[6] = "p4_add_si_fast_period_out_mon";
member_group_si_fast[7] = "p4_add_si_fast_period_out_year";
member_group_si_fast[8] = "p4_add_si_fast_work_tc";
member_group_si_fast[9] = "p4_add_si_fast_work_en";

var member_group_si_full = new Array();
member_group_si_full[0] = "p4_add_si_full_iis_id";
member_group_si_full[1] = "p4_add_si_full_name_tc";
member_group_si_full[2] = "p4_add_si_full_name_en";
member_group_si_full[3] = "p4_add_si_full_aka_tc";
member_group_si_full[4] = "p4_add_si_full_aka_en";
member_group_si_full[5] = "p4_add_si_full_email";
member_group_si_full[6] = "p4_add_si_full_tel";
member_group_si_full[7] = "p4_add_si_full_cell";
member_group_si_full[8] = "p4_add_si_full_period_in_mon";
member_group_si_full[9] = "p4_add_si_full_period_in_year";
member_group_si_full[10] = "p4_add_si_full_period_out_mon";
member_group_si_full[11] = "p4_add_si_full_period_out_year";
member_group_si_full[12] = "p4_add_si_full_work_tc";
member_group_si_full[13] = "p4_add_si_full_work_en";
member_group_si_full[14] = "p4_add_si_full_research";
member_group_si_full[15] = "p4_add_si_full_birthday";
member_group_si_full[16] = "p4_add_si_full_email_stable";


var member_group_si_full_mod = new Array();
member_group_si_full_mod[0] = "p4_mod_si_full_iis_id";
member_group_si_full_mod[1] = "p4_mod_si_full_name_tc";
member_group_si_full_mod[2] = "p4_mod_si_full_name_en";
member_group_si_full_mod[3] = "p4_mod_si_full_aka_tc";
member_group_si_full_mod[4] = "p4_mod_si_full_aka_en";
member_group_si_full_mod[5] = "p4_mod_si_full_email";
member_group_si_full_mod[6] = "p4_mod_si_full_tel";
member_group_si_full_mod[7] = "p4_mod_si_full_cell";
member_group_si_full_mod[8] = "p4_mod_si_full_period_in_mon";
member_group_si_full_mod[9] = "p4_mod_si_full_period_in_year";
member_group_si_full_mod[10] = "p4_mod_si_full_period_out_mon";
member_group_si_full_mod[11] = "p4_mod_si_full_period_out_year";
member_group_si_full_mod[12] = "p4_mod_si_full_work_tc";
member_group_si_full_mod[13] = "p4_mod_si_full_work_en";
member_group_si_full_mod[14] = "p4_mod_si_full_research";
member_group_si_full_mod[15] = "p4_mod_si_full_type";
member_group_si_full_mod[16] = "p4_mod_si_full_user_id";
member_group_si_full_mod[17] = "p4_mod_si_full_birthday";
member_group_si_full_mod[18] = "p4_mod_si_full_email_stable";

var resources_group = new Array();
resources_group[0] = "p6_subject_add";
resources_group[1] = "p6_link_add";
resources_group[2] = "p6_visible_add";
resources_group[3] = "p6_top_add";
resources_group[4] = "p6_lang_add";
resources_group[5] = "p6_private_add";
resources_group[6] = "p6_img_link_add";

var download_group = new Array();
download_group[0] = "p7_subject_add";
download_group[1] = "p7_link_add";
download_group[2] = "p7_visible_add";
download_group[3] = "p7_top_add";
download_group[4] = "p7_lang_add";

var doc_mod_group = new Array();
doc_mod_group[0] = "p9_mod_id";
doc_mod_group[1] = "p9_edit_id";
doc_mod_group[2] = "p9_edit_cate";
doc_mod_group[3] = "p9_content_tc";
doc_mod_group[4] = "p9_content_en";

var doc_add_group = new Array();
doc_add_group[0] = "p9_add_id";
doc_add_group[1] = "p9_add_cate";
doc_add_group[2] = "p9_add_content_tc";
doc_add_group[3] = "p9_add_content_en";


// 隱藏 div (直接傳object)
function hide_div(o){
	o.style.display = "none";
}

function hide_div_x(id){
	e = document.getElementById(id );
	if(e != null){
		e.style.display = "none";
	}
}

// 顯示 div 
function show_div(id){
	e = document.getElementById(id );
	if(e != null){
		e.style.display = "block";
	}
}

// 載入與選單選項相關之網頁內容
function get_content(no,after,data){
		
		request_type = 0;		// 請求方式為 "傳送"
		target_type = 0;		// 請求目標為 "系統"

		if(no != position){
			e = document.getElementById(container_name + position );
			e.style.display = "none";
		}

        var query = "";
        if( data != null ) {
            var querys = new Array();
            for( key in data ) { querys.push(key+"="+data[key]); }
            query = "?" + querys.join("&");
        }
		position = no;
		call_ajax(remote_file_name + no + ".html"+query,"" , "GET", container_name + no,after);

}


function update_div(id, remote_file_name, type_id){
	// console.log( 'update_div', id, remote_file_name, type_id );
		target_type = type_id;		
		param = "";

		if(id == "p2_news_tc" || id == "p6_news_tc"){
				param = "?lang=TC";

		}else if(id == "p2_news_en" || id == "p6_news_en"){
				param = "?lang=EN";

		}else if(id == "p4_mod_member"){
//			remote_file_name = encodeURIComponent(remote_file_name);

		}
			request_type = 4;		// 請求方式為 "重新讀取"
//		alert(remote_file_name);
		call_ajax(remote_file_name + param,"" , "GET", id);

}

// 選取選單物件時樣式改變
function select_item(no,after,data){

		i = 1;
		while( i <= total_menu_item){

			// 把所有物件樣式設為一般
			e = document.getElementById("menu_item" + i );

			if(e != null){
				e.className = item_style;
			}
			i++;
		}
		
		// 設定指定物件為選取樣式
		e = document.getElementById("menu_item" + no );

		if(e != null){
			e.className = item_style_selected;

			// 抓取相關網頁並放到 "main_content" 之中.
			get_content(no,after,data);
		}

}

function sync_input(id, target, type_id, time_tag, also_update){
	console.log( 'sync_input' );
	e = document.getElementById(id);
	param = "id=" + id + "&data=" + encodeURIComponent(e.value);
	request_type = 2;		// 請求方式為 "更新"
	target_type = type_id;

	ajax_id_related = "";	// 初始化
//	alert(also_update);
	if(also_update != undefined && also_update != ""){
		ajax_id_related = also_update;
	}

	time_tag_id = "";		// 初始化
	if(time_tag == undefined){
		time_tag_id = id + '_t';
	}else if (time_tag != ""){
		time_tag_id = time_tag;
	}

//	alert(time_tag_id);
	console.log( target, param, id);

	call_ajax(target, param , "POST", id);
}

function sync_delete(id, target, type_id, record_id, renew_group){
	console.log( 'sync_delete' );

	// 判斷不同id, 作出不同處理
	if(id.indexOf("p2_btn_del") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_2.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);

	}else if(id.indexOf("p13_btn_del") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_13.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);

	}else if(id.indexOf("p4_btn_del_member") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_4.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);

	}else if(id.indexOf("p5_btn_del_publication") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_5.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);

	}else if(id.indexOf("p7_btn_del") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_7.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);

	}else if(id.indexOf("p6_btn_del") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_6.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);

	}else if(id.indexOf("p8_btn_del") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_8_2.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);

	}else if(id.indexOf("p9_del") != -1){

		param = "id=" + id + "&data=" + encodeURIComponent(record_id);
		//alert(param);
		request_type = 3;		// 請求方式為 "刪除"
		target_type = type_id;
		renew_path = 'lib/renew_9.html';
		renew_div_id = renew_group;

		call_ajax(target, param , "POST", id);
	}else{

	}
	
}

function sync_toggle(id, target, type_id, record_id, change_img_src){

	if(id.indexOf("p13_btn_toggle_show") != -1 || id.indexOf("p2_btn_toggle_show") != -1 || id.indexOf("p2_btn_toggle_top") != -1 || id.indexOf("p7_btn_toggle_show") != -1 || id.indexOf("p7_btn_toggle_top") != -1 || id.indexOf("p6_btn_toggle_show") != -1 || id.indexOf("p6_btn_toggle_top") != -1 ||    id.indexOf("p8_btn_toggle_show") != -1){
		
		img_x = document.getElementById(id);
		console.log(img_x);

		if(img_x != null){
			img_src = img_x.src
			renew_img_src = "";

			if(img_src.indexOf("_n") != -1){
				toggle_value = 1;
				renew_img_src = change_img_src + ".gif";
			}else{
				toggle_value = 0;
				renew_img_src = change_img_src + "_n.gif";
			}

			//alert(renew_img_src);

			param = "id=" + id + "&data=" + encodeURIComponent(record_id) + "||" + encodeURIComponent(toggle_value);
			request_type = 2;		// 請求方式為 "更新"
			target_type = type_id;
			call_ajax(target, param , "POST", id);
		}


	}else if(id.indexOf("p6_btn_private")  != -1){

		img_x = document.getElementById(id);

		if(img_x != null){
			img_src = img_x.src
			renew_img_src = "";

			if(img_src.indexOf("private.gif") != -1){
				toggle_value = 0;
				renew_img_src = change_img_src + "acad.gif";
			}else if(img_src.indexOf("acad.gif") != -1){
				toggle_value = 2;
				renew_img_src = change_img_src + "edu.gif";
			}else if(img_src.indexOf("edu.gif") != -1){
				toggle_value = 3;
				renew_img_src = change_img_src + "asoc.gif";
			}else if(img_src.indexOf("asoc.gif") != -1){
				toggle_value = 1;
				renew_img_src = change_img_src + "private.gif";
			}

		//	alert(toggle_value);
		//	alert(renew_img_src);

			param = "id=" + id + "&data=" + encodeURIComponent(record_id) + "||" + encodeURIComponent(toggle_value);
			request_type = 2;		// 請求方式為 "更新"
			target_type = type_id;
			call_ajax(target, param , "POST", id);
		}


	}else if(id.indexOf("p5_btn_toggle_show") != -1){

		img_x = document.getElementById(id);

		if(img_x != null){
			img_src = img_x.src
			renew_img_src = "";

			if(img_src.indexOf("_n") != -1){
				toggle_value = 1;
				renew_img_src = change_img_src + ".gif";
			}else{
				toggle_value = 0;
				renew_img_src = change_img_src + "_n.gif";
			}

			//alert(renew_img_src);

			param = "id=" + id + "&data=" + encodeURIComponent(record_id) + "||" + encodeURIComponent(toggle_value);
			request_type = 2;		// 請求方式為 "更新"
			target_type = type_id;
			call_ajax(target, param , "POST", id);
		}

	}else if(id.indexOf("p7_btn_toggle_lang") != -1){

		img_x = document.getElementById(id);

		if(img_x != null){
			img_src = img_x.src
			renew_img_src = "";
			
			// TC -> EN -> BOTH -> TC

			if(img_src.indexOf("TC") != -1){
				toggle_value = "EN";
				renew_img_src = change_img_src + "EN.gif";
			}else if(img_src.indexOf("EN") != -1){
				toggle_value = "BOTH";
				renew_img_src = change_img_src + "BOTH.gif";
			}else{
				toggle_value = "TC";
				renew_img_src = change_img_src + "TC.gif";

			}

			//alert(renew_img_src);

			param = "id=" + id + "&data=" + encodeURIComponent(record_id) + "||" + encodeURIComponent(toggle_value);
			request_type = 2;		// 請求方式為 "更新"
			target_type = type_id;
			call_ajax(target, param , "POST", id);
		}

	}else{
	
	}

}

function sync_input_group(id, target, type_id, time_tag){

	target_type = type_id;

	if(id == "p1_contact_group_tc"){
		e1 = document.getElementById(contact_group[0]+"_tc");
		e2 = document.getElementById(contact_group[1]+"_tc");
		e3 = document.getElementById(contact_group[2]+"_tc");
		e4 = document.getElementById(contact_group[3]+"_tc");
		if(e1 != null && e2 != null && e3 != null && e4 != null){	
			param = "id=" + id + "&data=" + encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value) + "||" + encodeURIComponent(e3.value) + "||" + encodeURIComponent(e4.value);
			//alert("OK!");
		}else{
			alert("Error para name");
		}
		request_type = 2;		// 請求方式為 "更新"

	}else if(id == "p1_contact_group_en"){
		e1 = document.getElementById(contact_group[0]+"_en");
		e2 = document.getElementById(contact_group[1]+"_en");
		e3 = document.getElementById(contact_group[2]+"_en");
		e4 = document.getElementById(contact_group[3]+"_en");
		if(e1 != null && e2 != null && e3 != null && e4 != null){	
			param = "id=" + id + "&data=" + encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value) + "||" + encodeURIComponent(e3.value) + "||" + encodeURIComponent(e4.value);
		}else{
			alert("Error para name");
		}
		request_type = 2;		// 請求方式為 "更新"

	}else if(id == "p2_news_group"){
		e1 = document.getElementById(news_group[0]);
		e2 = document.getElementById(news_group[1]);
		e3 = document.getElementById(news_group[2]);
		e4 = document.getElementById(news_group[3]);
		e5 = document.getElementById(news_group[4]);
		if(e1 != null && e2 != null && e3 != null && e4 != null && e5 != null){	

			if(e5.value == "TC"){
				renew_div_id = "p2_news_tc";
			}else if (e5.value == "EN"){
				renew_div_id = "p2_news_en";
			}
			renew_path = 'lib/renew_2.html';
			//alert( e3.checked + "," + e4.checked);
			if(e3.checked == true){
				e3_v = 1;
			}else{
				e3_v = 0;
			}
			if(e4.checked == true){
				e4_v = 1;
			}else{
				e4_v = 0;
			}
		//	alert(e3_v + "," + e4_v);
			param = "id=" + id + "&data=" + encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value) + "||" + encodeURIComponent(e3_v) + "||" + encodeURIComponent(e4_v) + "||" + encodeURIComponent(e5.value);
		}else{
			alert("Error para name");
		}

		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p13_main_slogan_group"){
		e1 = document.getElementById(slogan_group[0]);
		e2 = document.getElementById(slogan_group[1]);
		console.log(e1.value);
		console.log(e2.checked);
		if(e1 != null && e2 != null){	
			renew_div_id = "p13_main_slogan";
			renew_path = 'lib/renew_13.html';
			//alert( e3.checked + "," + e4.checked);
			if(e2.checked == true){
				e2_v = 1;
			}else{
				e2_v = 0;
			}
		//	alert(e3_v + "," + e4_v);
			param = "id=" + id + "&data=" + encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2_v);
		}else{
			alert("Error para name");
		}

		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p6_news_group"){
		e1 = document.getElementById(resources_group[0]);
		e2 = document.getElementById(resources_group[1]);
		e3 = document.getElementById(resources_group[2]);
		e4 = document.getElementById(resources_group[3]);
		e5 = document.getElementById(resources_group[4]);
		e6 = document.getElementById(resources_group[5]);
		e7 = document.getElementById(resources_group[6]);

		
		if(e1 != null && e2 != null && e3 != null && e4 != null && e5 != null && e6 != null){	

			if(e5.value == "TC"){
				renew_div_id = "p6_news_tc";
			}else if (e5.value == "EN"){
				renew_div_id = "p6_news_en";
			}
			renew_path = 'lib/renew_6.html';
			//alert( e3.checked + "," + e4.checked);
			if(e3.checked == true){
				e3_v = 1;
			}else{
				e3_v = 0;
			}
			if(e4.checked == true){
				e4_v = 1;
			}else{
				e4_v = 0;
			}
		//	alert(e3_v + "," + e4_v);
			param = "id=" + id + "&data=" + encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value) + "||" + encodeURIComponent(e3_v) + "||" + encodeURIComponent(e4_v) + "||" + encodeURIComponent(e5.value) + "||" + encodeURIComponent(e6.value) +"||" + encodeURIComponent(e7.value);
		}else{
			alert("Error para name");
		}

		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p4_add_pd_fast"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_pd_fast.length){
			e = document.getElementById(member_group_pd_fast[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_pd_fast[i]);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p4_add_pd_full"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_pd_full.length){
			e = document.getElementById(member_group_pd_full[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_pd_full[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"
		
	}else if(id == "p4_add_ra_fast"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_ra_fast.length){
			e = document.getElementById(member_group_ra_fast[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_ra_fast[i]);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p4_add_ra_full"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_ra_full.length){
			e = document.getElementById(member_group_ra_full[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_ra_full[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"
		
	}else if(id == "p4_add_al_fast"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_al_fast.length){
			e = document.getElementById(member_group_al_fast[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_al_fast[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p4_add_al_full"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_al_full.length){
			e = document.getElementById(member_group_al_full[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_al_full[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p4_add_si_fast"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_si_fast.length){
			e = document.getElementById(member_group_si_fast[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_si_fast[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p4_add_si_full"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_si_full.length){
			e = document.getElementById(member_group_si_full[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_si_full[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p4_mod_ra_full"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_ra_full_mod.length){
			e = document.getElementById(member_group_ra_full_mod[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_ra_full_mod[i] + ", " + i);

				break;
			}
			i = i + 1;
		}


		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 2;		// 請求方式為 "更新"

	}else if(id == "p4_mod_al_full"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_al_full_mod.length){
			e = document.getElementById(member_group_al_full_mod[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_al_full_mod[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 2;		// 請求方式為 "更新"

	}else if(id == "p4_mod_si_full"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < member_group_si_full_mod.length){
			e = document.getElementById(member_group_si_full_mod[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + member_group_si_full_mod[i] + ", " + i);

				break;
			}
			i = i + 1;
		}

		renew_path = 'lib/renew_4.html';
		renew_div_id = "p4_members";
		request_type = 2;		// 請求方式為 "更新"

	}else if(id == "p5_add_publication"){

		e1 = document.getElementById("p5_add_abstract");
		e2 = document.getElementById("p5_add_bibtex");

		param = "id=" + id + "&data=";

		if(e1 != null && e2 != null){
			param += encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value);
			renew_path = 'lib/renew_5.html';
			renew_div_id = "p5_public";
			request_type = 1;		// 請求方式為 "儲存"

		}

	}else if(id == "p5_mod_publication"){

		e1 = document.getElementById("p5_mod_abstract");
		e2 = document.getElementById("p5_mod_bibtex");
		e3 = document.getElementById("p5_mod_id");

		param = "id=" + id + "&data=";

		if(e1 != null && e2 != null){
			param += encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value) + "||" + encodeURIComponent(e3.value);
			renew_path = 'lib/renew_5.html';
			renew_div_id = "p5_public";
			request_type = 2;		// 請求方式為 "儲存"

		}

	}else if(id == "p7_news_group"){
		e1 = document.getElementById(download_group[0]);
		e2 = document.getElementById(download_group[1]);
		e3 = document.getElementById(download_group[2]);
		e4 = document.getElementById(download_group[3]);
		e5 = document.getElementById(download_group[4]);
		if(e1 != null && e2 != null && e3 != null && e4 != null && e5 != null){	


		renew_div_id = "p7_news";

		renew_path = 'lib/renew_7.html';

			//alert( e3.checked + "," + e4.checked);
			if(e3.checked == true){
				e3_v = 1;
			}else{
				e3_v = 0;
			}
			if(e4.checked == true){
				e4_v = 1;
			}else{
				e4_v = 0;
			}
		//	alert(e3_v + "," + e4_v);
			param = "id=" + id + "&data=" + encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value) + "||" + encodeURIComponent(e3_v) + "||" + encodeURIComponent(e4_v) + "||" + encodeURIComponent(e5.value);
		}else{
			alert("Error para name");
		}

		request_type = 1;		// 請求方式為 "儲存"

	}else if(id == "p8_news_group"){

		e1 = document.getElementById("p8_user_id_add");
		e2 = document.getElementById("p8_password_add");

		param = "id=" + id + "&data=";

		if(e1 != null && e2 != null){
			param += encodeURIComponent(e1.value) + "||" + encodeURIComponent(e2.value);
			renew_path = 'lib/renew_8_2.html';
			renew_div_id = "p8_users";
			request_type = 2;		// 請求方式為 "儲存"

		}

	}else if(id == "p9_mod_doc"){
		i = 0;
		param = "id=" + id + "&data=";
		while(i < doc_mod_group.length){
			e = document.getElementById(doc_mod_group[i]);
			
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + doc_mod_group[i]);

				break;
			}
			i = i + 1;
		}

	renew_div_id = "";
	request_type = 2;		// 請求方式為 "更新"

	}else if(id == "p9_add_doc"){
		i = 0;
		param = "id=" + id + "&data=";

		
		while(i < doc_add_group.length){
			e = document.getElementById(doc_add_group[i]);
		//	alert(e.value);
			if(e != null){
				param += encodeURIComponent(e.value) + "||";
			}else{
				alert("Error para name:" + doc_add_group[i]);

				break;
			}
			i = i + 1;
		}

	renew_path = 'lib/renew_9.html';
	renew_div_id = "p9_all_doc";
	request_type = 2;		// 請求方式為 "更新"

	}else{

	}

//	alert(type_id);
	target_type = type_id;

	if(time_tag == undefined){
		time_tag_id = id + '_t';
	}else{
		time_tag_id = time_tag;
	}

	call_ajax(target, param , "POST", id);
}

// 提供快速叫出訊息欄的方法
function pop_msg(icon, msg, time){
	d = document.getElementById("loading");
	m = document.getElementById("load_msg");
	i = document.getElementById("load_img");

	if( d != null && d != null && i != null ) {
        i.src = icon;
        m.innerHTML = msg;
        d.style.display = "block";
        if( time != 0 ) { setTimeout("hide_div(d)", time); }
	}
}

function get_offset(){
	var x,y;

	if (self.pageYOffset) // all except Explorer
	{
		x = self.pageXOffset;
		y = self.pageYOffset;
	}
	else if (document.documentElement && document.documentElement.scrollTop)
		// Explorer 6 Strict
	{
		x = document.documentElement.scrollLeft;
		y = document.documentElement.scrollTop;
	}
	else if (document.body) // all other Explorers
	{
		x = document.body.scrollLeft;
		y = document.body.scrollTop;
	}

	d = document.getElementById("loading");

	if(y > 100){
		d.style.top = (y-71) +  "px";

	}else{
		d.style.top = '0px';
	}
//	alert(d.style.top + "," + y);
}

function toggle_edit(div_off, div_on){
	t1 = document.getElementById(div_off);
	t2 = document.getElementById(div_on);
	t3 = document.getElementById(div_on + "_field");

	if(t1 != null && t2 != null){
		t1.style.display = "none";
		t2.style.display = "block";
		if(t3 != null){
			t3.focus();
		}
	}

}


function clear_add2(type_id){
	if(type_id == 18){
		e1 = document.getElementById("p5_add_abstract");
		e2 = document.getElementById("p5_add_bibtex");

		if(e1 != null && e2 != null){
				e1.value = "";
				e2.value = "";
		}
	}else if(type_id == 19){
		a2 = document.getElementById("p5_mod_publication");
		if(a2 != null){
			a2.style.display = "none";
		}
	}
}

// only used in panel4
function clear_add(type_id){
	if(type_id == 13){
		i = 0;
		while(i < member_group_ra_full.length){
			e = document.getElementById(member_group_ra_full[i]);		
			if(e != null){
				e.value = "";
			}
			i = i + 1;
		}	
	}else if(type_id == 12){	
		i = 0;
		while(i < member_group_ra_fast.length){
			e = document.getElementById(member_group_ra_fast[i]);		
			if(e != null){
				e.value = "";
			}
			i = i + 1;
		}
	}else if(type_id == 23){	
		i = 0;
		while(i < member_group_pd_full.length){
			e = document.getElementById(member_group_pd_full[i]);		
			if(e != null){
				e.value = "";
			}
			i = i + 1;
		}	
	}else if(type_id == 22){	
		i = 0;
		while(i < member_group_pd_fast.length){
			e = document.getElementById(member_group_pd_fast[i]);		
			if(e != null){
				e.value = "";
			}
			i = i + 1;
		}		
	}else if(type_id == 14){	
		i = 0;
		while(i < member_group_al_fast.length){
			e = document.getElementById(member_group_al_fast[i]);		
			if(e != null){
				e.value = "";
			}
			i = i + 1;
		}	
	}else if(type_id == 15){	
		i = 0;
		while(i < member_group_al_full.length){
			e = document.getElementById(member_group_al_full[i]);		
			if(e != null){
				e.value = "";
			}
			i = i + 1;
		}	
	}else if(type_id == 17){	
		a5 = document.getElementById("p4_mod_member");
		
		if(a5 != null){
			a5.style.display = "none";
		}

	}else if(type_id == 42){	
		i = 0;
		while(i < doc_add_group.length){
			e = document.getElementById(doc_add_group[i]);		
			if(e != null){
				e.value = "";
			}
			i = i + 1;
		}	
	}
}

// only used in panel4
function switch_add(id){
	
	ax = document.getElementById(id);
	a1 = document.getElementById("p4_add_ra_full");
	a2 = document.getElementById("p4_add_ra_fast");
	a3 = document.getElementById("p4_add_al_full");
	a4 = document.getElementById("p4_add_al_fast");
	a5 = document.getElementById("p4_mod_member");
	a6 = document.getElementById("p4_add_si_fast");

	bx = document.getElementById(id + "_btn");
	b1 = document.getElementById("p4_add_ra_full_btn");
	b2 = document.getElementById("p4_add_ra_fast_btn");
	b3 = document.getElementById("p4_add_al_full_btn");
	b4 = document.getElementById("p4_add_al_fast_btn");
	b5 = document.getElementById("p4_add_si_fast_btn");
	
	if(a1 != null && a2 != null && a3 != null && a4 != null && a6 != null && ax != null){
	
		ax_show = ax.style.display;
		a1.style.display = "none";
		a2.style.display = "none";
		a3.style.display = "none";
		a4.style.display = "none";	
		a5.style.display = "none";	
		a6.style.display = "none";	

		b1.style.fontWeight = "normal";
		b2.style.fontWeight = "normal";
		b3.style.fontWeight = "normal";
		b4.style.fontWeight = "normal";
		b5.style.fontWeight = "normal";
		
		if(ax_show == "none"){
			ax.style.display = "block";
			bx.style.fontWeight = "bold";

		}else{
			ax.style.display = "none";
		}	
	}

}

// only used in panel5
function switch_add2(id){
	
	ax = document.getElementById(id);
	a1 = document.getElementById("p5_add_publication");
	a2 = document.getElementById("p5_mod_publication");


	bx = document.getElementById(id + "_btn");
	b1 = document.getElementById("p5_add_publication_btn");
	
	if(a1 != null && a2 != null && ax != null){

	//	alert("OK!");
	
		ax_show = ax.style.display;
		a1.style.display = "none";
		a2.style.display = "none";

		b1.style.fontWeight = "normal";

		
		if(ax_show == "none"){
			ax.style.display = "block";
			bx.style.fontWeight = "bold";
			c1 = document.getElementById("p5_add_abstract");
			c2 = document.getElementById("p5_add_bibtex");

			if(c1 != null && c2 != null){
				c1.value = "";
				c2.value = "";
			}


		}else{
			ax.style.display = "none";
		}	
	}

}

function toggle_div(id1, id2){

	c1 = document.getElementById(id1);
	c2 = document.getElementById(id2);

	if(c1 != null && c2 != null){
		if(c1.style.display == "block" && c2.style.display == "none"){
			c1.style.display = "none";
			c2.style.display = "block";
		}else if(c2.style.display == "block" && c1.style.display == "none"){
			c2.style.display = "none";
			c1.style.display = "block";
		}
	}else{
//		alert("!!");
	}
}

function toggle_bg(no){
	c1 = document.getElementById("p9_doc_item_" + no);
	
	
	if(c1 != null){
//		alert("!!");
		if(edit_doc != no){
			c1.style.backgroundImage="url(../pic/doc_rol.png)";
		}
	}
}

function toggle_bg2(no){
	c1 = document.getElementById("p9_doc_item_" + no);
	
	if(c1 != null){
		if(edit_doc != no){
			c1.style.backgroundImage="url(../pic/doc.png)";
		}
	}
}

function got_edit(no){
	
	c1 = document.getElementById("p9_doc_item_" + edit_doc);
	if(c1 != null ){
			c1.style.backgroundImage="url(../pic/doc.png)";		
	}
	
	c1 = document.getElementById("p9_doc_item_" + no);
	if(c1 != null){
		c1.style.backgroundImage="url(../pic/doc_now.png)";
		edit_doc = no;
		fin_add();
	}

	update_div('p9_mod_doc', 'lib/mod_9.html?id='+no, 41);

	
	show_div('p9_mod_doc');
	
}

function fin_edit(){
	
	c1 = document.getElementById("p9_doc_item_" + edit_doc);
	if(c1 != null ){
			c1.style.backgroundImage="url(../pic/doc.png)";		
	}

	
	c1 = document.getElementById("p9_mod_doc");	
	if(c1 != null){
		c1.style.display = "none";
		edit_doc = -1;

	}
}

function fin_add(){
	c1 = document.getElementById("p9_add_doc");	
	if(c1 != null){
		c1.style.display = "none";		
	}	
}

function p9_add_go(){
	
	c1 = document.getElementById("p9_add_doc");	
	if(c1 != null){
		c1.style.display = "block";
		fin_edit();
		
	}
}