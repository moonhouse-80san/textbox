/**
 * popup으로 열렸을 경우 부모창의 위지윅에디터에 select된 멀티미디어 컴포넌트 코드를 체크하여
 * 있으면 가져와서 원하는 곳에 삽입
 **/
var selected_node = null;
function getTextbox() {
	// 부모 위지윅 에디터에서 선택된 영역이 있는지 확인
	if(typeof(opener)=="undefined") return;

	var node = opener.editorPrevNode;
	if(!node || node.nodeName != "DIV") return;

	selected_node = node;

	var use_folder = node.getAttribute("use_folder");
	var folder_opener = node.getAttribute("folder_opener");
	var folder_closer = node.getAttribute("folder_closer");
	if(folder_opener) folder_opener = folder_opener.replace(/&gt;/g,'>').replace(/&lt;/,'<').replace(/&quot;/,'"');
	if(folder_closer) folder_closer = folder_closer.replace(/&gt;/g,'>').replace(/&lt;/,'<').replace(/&quot;/,'"');
	var bold = node.getAttribute("bold");
	var color = node.getAttribute("color");
	var font = node.getAttribute("font");
	var lineheight = node.getAttribute("lineheight");
	var linebreak = node.getAttribute("linebreak");
	var margin = node.getAttribute("margin");
	var padding = node.getAttribute("padding");
	var border_style = node.getAttribute("border_style");
	var border_thickness = node.getAttribute("border_thickness");
	var border_color = node.getAttribute("border_color");
	var bg_color = node.getAttribute("bg_color");
	var text_color = node.getAttribute("text_color");
	var remove_whitespace = node.getAttribute("remove_whitespace");
	var title = node.getAttribute("title");
	var copy_button = node.getAttribute("copy_button") || 'Y';

	// 제목 필드 설정
	if(title) xGetElementById("textbox_title").value = title;

	if(use_folder=="Y") xGetElementById("textbox_use").checked = true;
	else xGetElementById("textbox_use").checked = false;
	toggle_folder( xGetElementById("textbox_use") );

	if(bold=="Y") xGetElementById("textbox_bold").checked = true;
	switch(color) {
	  case "red" :
		  xGetElementById("textbox_color_red").checked = true;
		break;
	  case "yellow" :
		  xGetElementById("textbox_color_yellow").checked = true;
		break;
	  case "green" :
		  xGetElementById("textbox_color_green").checked = true;
		break;
	  default :
		  xGetElementById("textbox_color_blue").checked = true;
		break;
	}

	// 복사 버튼 라디오 버튼 설정
	if(copy_button == "Y") {
		xGetElementById("copy_button_yes").checked = true;
	} else {
		xGetElementById("copy_button_no").checked = true;
	}

	xGetElementById("textbox_opener").value = folder_opener;
	xGetElementById("textbox_closer").value = folder_closer;
	xGetElementById("textbox_font").value = font;
	xGetElementById("textbox_lineheight").value = lineheight;
	if (linebreak == 'Y') xGetElementById("textbox_linebreak").checked = true;
	else xGetElementById("textbox_linebreak").checked = false;
	xGetElementById("textbox_margin").value = margin;
	xGetElementById("textbox_padding").value = padding;

	switch(border_style) {
		case "solid" :
				xGetElementById("border_style_solid").checked = true;
			break;
		case "dotted" :
				xGetElementById("border_style_dotted").checked = true;
			break;
		case "left_solid" :
				xGetElementById("border_style_left_solid").checked = true;
			break;
		case "left_dotted" :
				xGetElementById("border_style_left_dotted").checked = true;
			break;
		default :
				xGetElementById("border_style_none").checked = true;
			break;
	}

	xGetElementById("border_thickness").value = border_thickness;

	select_color('border', border_color);
	select_color('bg', bg_color);
	select_color('text', text_color);
	
	// 빈칸 제거 라디오 버튼 설정
	if(remove_whitespace=="Y") {
		xGetElementById("remove_whitespace_yes").checked = true;
	} else {
		xGetElementById("remove_whitespace_no").checked = true;
	}
}

/* 추가 버튼 클릭시 부모창의 위지윅 에디터에 인용구 추가 */
function insertTextbox() {
	if(typeof(opener)=="undefined") return;

	var use_folder = "N";
	if(xGetElementById("textbox_use").checked) use_folder = "Y";

	var folder_opener = xGetElementById("textbox_opener").value;
	var folder_closer = xGetElementById("textbox_closer").value;
	if(!folder_opener||!folder_closer) use_folder = "N";

	folder_opener = folder_opener.replace(/>/g,'&gt;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
	folder_closer = folder_closer.replace(/>/g,'&gt;').replace(/</g,'&lt;').replace(/"/g,'&quot;');

	var bold = "N";
	if(xGetElementById("textbox_bold").checked) bold = "Y";
	var color = "blue";
	if(xGetElementById("textbox_color_red").checked) color = "red";
	if(xGetElementById("textbox_color_yellow").checked) color = "yellow";
	if(xGetElementById("textbox_color_green").checked) color = "green";

	var font = xGetElementById("textbox_font").value;
	var lineheight = xGetElementById("textbox_lineheight").value;
	var linebreak = (xGetElementById("textbox_linebreak").checked) ? 'Y' : 'N';
	var margin = parseInt(xGetElementById("textbox_margin").value,10);
	var padding = parseInt(xGetElementById("textbox_padding").value,10);
	var title = xGetElementById("textbox_title").value;
	var copy_button = "Y";
	if(xGetElementById("copy_button_no").checked) copy_button = "N";

	var border_style = "solid";
	if(xGetElementById("border_style_none").checked) border_style = "none";
	if(xGetElementById("border_style_solid").checked) border_style = "solid";
	if(xGetElementById("border_style_dotted").checked) border_style = "dotted";
	if(xGetElementById("border_style_left_solid").checked) border_style = "left_solid";
	if(xGetElementById("border_style_left_dotted").checked) border_style = "left_dotted";

	var border_thickness = parseInt(xGetElementById("border_thickness").value,10);
	var border_color = xGetElementById("border_color_input").value;
	var bg_color = xGetElementById("bg_color_input").value;
	var text_color = xGetElementById("text_color_input").value;
	
	var remove_whitespace = "N";
	if(xGetElementById("remove_whitespace_yes").checked) remove_whitespace = "Y";

	var content = "";
	if(selected_node) content = xInnerHtml(selected_node);
	else content = opener.editorGetSelectedHtml(opener.editorPrevSrl);

	var style = "margin: " + margin + "px; padding: " + padding + "px; background-color: #" + bg_color + ";";
	if (linebreak == 'N') style = "white-space: nowrap; overflow: auto; " + style;
	else style = "white-space: normal; scroll: auto; " + style;
	if (lineheight.length) style = "line-height: " + lineheight + "; " + style;
	if (font.length) style = "font-family: " + font + "; " + style;
	if (text_color.length) style += "color: #" + text_color + "; ";

	switch(border_style) {
		case "solid" :
				style += "border: " + border_thickness + "px solid #" + border_color + ";";
			break;
		case "dotted" :
				style += "border: " + border_thickness + "px dotted #" + border_color + ";";
			break;
		case "left_solid" :
				style += "border-left: " + border_thickness + "px solid #" + border_color + ";";
			break;
		case "left_dotted" :
				style += "border-left: " + border_thickness + "px dotted #" + border_color + ";";
			break;
	}

	if(!content) content = "&nbsp;";

	var text = '<div editor_component="textbox" use_folder="' + use_folder
		+ '" folder_opener="' + folder_opener + '" folder_closer="' + folder_closer
		+ '" bold="' + bold + '" color="' + color + '" font="' + font + '" lineheight="' + lineheight
		+ '" linebreak="' + linebreak + '" margin="' + margin + '" padding="' + padding + '" border_style="' + border_style
		+ '" border_thickness="' + border_thickness + '" border_color="' + border_color
		+ '" bg_color="' + bg_color + '" text_color="' + text_color 
		+ '" remove_whitespace="' + remove_whitespace 
		+ '" copy_button="' + copy_button
		+ '" title="' + encodeURIComponent(title) 
		+ '" style="' + style + '">' + content + '</div><br />';

	if (selected_node) {
		selected_node.setAttribute("use_folder", use_folder);
		selected_node.setAttribute("folder_opener", folder_opener);
		selected_node.setAttribute("folder_closer", folder_closer);
		selected_node.setAttribute("bold", bold);
		selected_node.setAttribute("color", color);
		selected_node.setAttribute("font", font);
		selected_node.setAttribute("lineheight", lineheight);
		selected_node.setAttribute("linebreak", linebreak);
		selected_node.setAttribute("margin", margin);
		selected_node.setAttribute("padding", padding);
		selected_node.setAttribute("border_style", border_style);
		selected_node.setAttribute("border_thickness", border_thickness);
		selected_node.setAttribute("border_color", border_color);
		selected_node.setAttribute("bg_color", bg_color);
		selected_node.setAttribute("text_color", text_color);
		selected_node.setAttribute("remove_whitespace", remove_whitespace);
		selected_node.setAttribute("copy_button", copy_button);
		selected_node.setAttribute("title", title);
		selected_node.setAttribute("style", style);

		if (font.length) selected_node.style.fontFamily = font;
		if (lineheight.length) selected_node.style.lineHeight = lineheight;
		if (linebreak == 'N')
			selected_node.style.whiteSpace = 'nowrap';
		else
			selected_node.style.whiteSpace = 'normal';
		selected_node.style.scroll = 'auto';
		selected_node.style.margin = margin + "px";
		selected_node.style.padding = padding + "px";
		selected_node.style.backgroundColor = "#" + bg_color;
		selected_node.style.color = "#" + text_color;

		selected_node.style.borderStyle = "none";
		selected_node.style.borderWidth = "0";

		switch(border_style) {
			case "solid" :
					selected_node.style.borderStyle = "solid";
					selected_node.style.borderWidth = border_thickness + "px";
					selected_node.style.borderColor = "#" + border_color;
				break;
			case "dotted" :
					selected_node.style.borderStyle = "dotted";
					selected_node.style.borderWidth = border_thickness + "px";
					selected_node.style.borderColor = "#" + border_color;
				break;
			case "left_solid" :
					selected_node.style.borderLeftStyle = "solid";
					selected_node.style.borderLeftWidth = border_thickness + "px";
					selected_node.style.borderLeftColor = "#" + border_color;
				break;
			case "left_dotted" :
					selected_node.style.borderLeftStyle = "dotted";
					selected_node.style.borderLeftWidth = border_thickness + "px";
					selected_node.style.borderCLeftColor = "#" + border_color;
				break;
			default :
					selected_node.style.borderStyle = "solid";
					selected_node.style.borderWidth = "0";
					selected_node.style.borderColor = "#" + border_color;
				break;
		}

		opener.editorFocus(opener.editorPrevSrl);

	} else {
		opener.editorFocus(opener.editorPrevSrl);
		var iframe_obj = opener.editorGetIFrame(opener.editorPrevSrl)
		opener.editorReplaceHTML(iframe_obj, text);
		opener.editorFocus(opener.editorPrevSrl);
	}

	window.close();
}

/* 색상 클릭시 */
function select_color(type, code) {
	xGetElementById(type + "_preview_color").style.backgroundColor = "#" + code;
	xGetElementById(type + "_color_input").value = code;

	if(type=="border") {
		xGetElementById("border_style_solid_icon").style.backgroundColor = "#" + code;
		xGetElementById("border_style_dotted_icon").style.backgroundColor = "#" + code;
		xGetElementById("border_style_left_solid_icon").style.backgroundColor = "#" + code;
		xGetElementById("border_style_left_dotted_icon").style.backgroundColor = "#" + code;
	}
}

/* 수동 색상 변경시 */
function manual_select_color(type, obj) {
	if(obj.value.length!=6) return;
	code = obj.value;
	xGetElementById(type + "_preview_color").style.backgroundColor = "#" + code;

	if(type=="border") {
		xGetElementById("border_style_solid_icon").style.backgroundColor = "#" + code;
		xGetElementById("border_style_dotted_icon").style.backgroundColor = "#" + code;
		xGetElementById("border_style_left_solid_icon").style.backgroundColor = "#" + code;
		xGetElementById("border_style_left_dotted_icon").style.backgroundColor = "#" + code;
	}
}

/* 색상표를 출력 */
function printColor(type, blank_img_src) {
	var colorTable = new Array('22','44','66','88','AA','CC','EE');
	var html = "";

	for(var i=0;i<8;i+=1) html += printColorBlock(type, i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16), blank_img_src);

	for(var i=0; i<colorTable.length; i+=3) {
		for(var j=0; j<colorTable.length; j+=2) {
			for(var k=0; k<colorTable.length; k++) {
				var code = colorTable[i] + colorTable[j] + colorTable[k];
				html += printColorBlock(type, code, blank_img_src);
			}
		}
	}

	for(var i=8;i<16;i+=1) html += printColorBlock(type, i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16)+i.toString(16), blank_img_src);

	document.write(html);
}

/* 개별 색상 block 출력 함수 */
function printColorBlock(type, code, blank_img_src) {
	if (type == "bg") {
		return '<div style="float: left; background-color: #' + code + '"><img src="' + blank_img_src + "\" class=\"color_icon\" onmouseover=\"this.className='color_icon_over'\" onmouseout=\"this.className='color_icon'\" onclick=\"select_color('" + type + "', '" + code + "')\" alt=\"color\" \/><\/div>";
	} else {
		return '<div style="float: left; background-color: #' + code + '"><img src="' + blank_img_src + "\" class=\"color_icon\" onmouseover=\"this.className='color_icon_over'\" onmouseout=\"this.className='color_icon'\" onclick=\"select_color('" + type + "', '" + code + "')\" alt=\"color\" \/><\/div>";
	}
}

/* 폴더 여닫기 기능 toggle */
function toggle_folder(obj) {
	if(obj.checked) xGetElementById("folder_area").style.display = "block";
	else xGetElementById("folder_area").style.display = "none";
	setFixedPopupSize();
}

xAddEventListener(window, "load", getTextbox);