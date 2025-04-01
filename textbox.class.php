<?php
    /**
     * @class  textbox
     * @author mooo (hhyoon@kldp.org)
     * @brief  에디터에서 PRE 문단 기능 제공.
     **/

    class textbox extends EditorHandler {

        // editor_sequence 는 에디터에서 필수로 달고 다녀야 함....
        var $editor_sequence = 0;
        var $component_path = '';

        /**
         * @brief editor_sequence과 컴포넌트의 경로를 받음
         **/
        function textbox($editor_sequence, $component_path) {
            $this->editor_sequence = $editor_sequence;
            $this->component_path = $component_path;
        }

        /**
         * @brief popup window요청시 popup window에 출력할 내용을 추가하면 된다
         **/
        function getPopupContent() {
            // 템플릿을 미리 컴파일해서 컴파일된 소스를 return
            $tpl_path = $this->component_path.'tpl';
            $tpl_file = 'popup.html';

            Context::set("tpl_path", $tpl_path);

            $oTemplate = &TemplateHandler::getInstance();
            return $oTemplate->compile($tpl_path, $tpl_file);
        }

        /**
         * @brief 에디터 컴포넌트가 별도의 고유 코드를 이용한다면 그 코드를 html로 변경하여 주는 method
         *
         * 이미지나 멀티미디어, 설문등 고유 코드가 필요한 에디터 컴포넌트는 고유코드를 내용에 추가하고 나서
         * DocumentModule::transContent() 에서 해당 컴포넌트의 transHtml() method를 호출하여 고유코드를 html로 변경
         **/
        function transHTML($xml_obj) {
            $use_folder = $xml_obj->attrs->use_folder;
            $folder_opener = $xml_obj->attrs->folder_opener;
            if(!$folder_opener) $folder_opener = "more...";
            $folder_closer = $xml_obj->attrs->folder_closer;
            if(!$folder_closer) $folder_closer= "close...";
            $bold = $xml_obj->attrs->bold;
            $color = $xml_obj->attrs->color;
            $font = $xml_obj->attrs->font;
            $lineheight = $xml_obj->attrs->lineheight;
            $linebreak = $xml_obj->attrs->linebreak;
            $margin = $xml_obj->attrs->margin;
            $padding = $xml_obj->attrs->padding;
            $border_style = $xml_obj->attrs->border_style;
            $border_thickness = $xml_obj->attrs->border_thickness;
            $border_color = $xml_obj->attrs->border_color;
            $bg_color = $xml_obj->attrs->bg_color;
			$remove_whitespace = $xml_obj->attrs->remove_whitespace;
            $body = $xml_obj->body;

			// 'Y'로 설정된 경우에만 빈칸 제거 기능 적용
			if($remove_whitespace == 'Y') {
				// HTML 엔티티 및 공백 처리
				// 연속된 &nbsp; 제거
				$body = preg_replace('/(&nbsp;)+/', ' ', $body);
			}

            $output = "";
            $style = sprintf('margin: %spx; margin-top: 26px; padding: %spx; background-color: #%s;', $margin, $padding, $bg_color);
            if ($linebreak == 'N') $style = "white-space: nowrap; overflow: auto; $style";
            if ($lineheight) $style = "line-height: $lineheight; $style";
            if ($font) $style = "font-family: $font; $style";

            switch($border_style) {
                case "solid" :
                        $style .= "border: ". $border_thickness."px solid #". $border_color.";";
                    break;
                case "dotted" :
                        $style .= "border: ". $border_thickness."px dotted #". $border_color.";";
                    break;
                case "left_solid" :
                        $style .= "border-left: ". $border_thickness."px solid #". $border_color.";";
                    break;
                case "left_dotted" :
                        $style .= "border-left: ". $border_thickness."px dotted #". $border_color.";";
                    break;
            }

            // 각 박스에 고유 ID 부여
            $box_id = 'textbox_' . rand(1000000, 9999999);

            // 복사 버튼을 위한 JavaScript 함수 추가
            $copy_button_script = '
            <script>
            function copyTextboxContent(boxId) {
                var box = document.getElementById(boxId);
                var content = box.textContent || box.innerText;
                
                // 복사 버튼 텍스트 제거 (버튼 텍스트가 복사되지 않도록)
                content = content.replace("복사하기", "").trim();

				// 원본 소스 코드 가져오기 (data-source 속성에서)
				var sourceCode = box.getAttribute("data-source");
                
                // 임시 textarea 생성
                var textarea = document.createElement("textarea");
                textarea.value = content;
                document.body.appendChild(textarea);
                
                // 선택 후 복사
                textarea.select();
                document.execCommand("copy");
                
                // 임시 요소 제거
                document.body.removeChild(textarea);
                
                // 복사 완료 메시지
                alert("내용이 클립보드에 복사되었습니다.");
            }
            </script>';
            
            // 한 번만 스크립트를 출력하기 위한 정적 변수
            static $script_added = false;
            if (!$script_added) {
                $output .= $copy_button_script;
                $script_added = true;
            }

            // 복사 버튼 스타일
            $button_style = 'position: absolute; top: -26px; right: 0px; padding: 3px 8px; ' .
                           'background-color: #F7F7F6; border: 1px solid #999; ' .
                           'border-radius: 3px; cursor: pointer; font-size: 12px;';

            if($use_folder == "Y") {
                $folder_id = rand(1000000,9999999);

                $folder_opener = str_replace("&amp;","&",$folder_opener);
                $folder_closer = str_replace("&amp;","&",$folder_closer);

                if($bold == "Y") $class = "bold";
                switch($color) {
                    case "red" :
                            $class .= " editor_red_text";
                        break;
                    case "yellow" :
                            $class .= " editor_yellow_text";
                        break;
                    case "green" :
                            $class .= " editor_green_text";
                        break;
                    default :
                            $class .= " editor_blue_text";
                        break;
                }

                $style .= "display:none; position: relative;";

                $folder_margin = sprintf("%spx %spx %spx %spx", $margin, $margin, 10, $margin);
                $output .= sprintf('<div id="folder_open_%s" style="margin: %s; display: block;"><a class="%s" href="#" onclick="zbxe_folder_open(\'%s\');return false;">%s</a></div>', $folder_id, $folder_margin, $class, $folder_id, $folder_opener);
                $output .= sprintf('<div id="folder_close_%s" style="margin: %s; display: none;"><a class="%s" href="#" onclick="zbxe_folder_close(\'%s\');return false;">%s</a></div>', $folder_id, $folder_margin, $class, $folder_id, $folder_closer);

                $output .= sprintf('<div style="%s" id="folder_%s">%s<button onclick="copyTextboxContent(\'folder_%s\')" style="%s">복사하기</button></div>', $style, $folder_id, $body, $folder_id, $button_style);
            } else {
                $output .= sprintf('<div style="%s position: relative;" id="%s">%s<button onclick="copyTextboxContent(\'%s\')" style="%s">복사하기</button></div>', $style, $box_id, $body, $box_id, $button_style);
            }
            return $output;
        }

    }
?>