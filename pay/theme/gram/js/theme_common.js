// 전체 선택, 개별 선택 체크박스 이미지와 실제 체크박스 체크되도록
$(document).ready(function(){
    $(".all_chk label").click(function(){
        $(this).toggleClass("click_on");

        if($(this).hasClass("click_on")) {
            $(".li_chk input[type=checkbox]").prop('checked', true);
            $(".li_chk label").addClass("click_on");
        } else {
            $(".li_chk input[type=checkbox]").prop('checked', false);
            $(".li_chk label").removeClass("click_on");
        }
    });
    $(".li_chk label").click(function(){
        $(this).toggleClass("click_on");

        var is_all = 1;
        $(".li_chk label").each(function(){
            if(!$(this).hasClass("click_on")) {
                is_all = 0;
            }
        });

        if(is_all) {
            $(".all_chk label").addClass("click_on");
        } else {
            $(".all_chk label").removeClass("click_on");
        }
    });
});






var win_card = function(href) {
	var new_win = window.open(href, 'win_memo', 'left=100,top=100,width=470,height=700,scrollbars=no');
	new_win.focus();
}

var win_card_cancel = function(href) {
	var new_win = window.open(href, 'win_memo', 'left=100,top=100,width=470,height=540,scrollbars=no');
	new_win.focus();
}


var win_receipt = function(href, id) {
	var new_win = window.open(href, id, 'left=100,top=100,width=400,height=700,scrollbars=1');
	new_win.focus();
}


var win_receipt2 = function(href, id) {
	var new_win = window.open(href, id, 'left=100,top=100,width=500,height=700,scrollbars=1');
	new_win.focus();
}