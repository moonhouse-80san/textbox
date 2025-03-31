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
         **/
        function transHTML($xml_obj) {
            // 기존 속성 추출 코드 유지
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
            
            // 새로운 속성: 빈칸 제거 옵션
            $remove_whitespace = $xml_obj->attrs->remove_whitespace;
            $body = $xml_obj->body;

            // 빈칸 제거 기능 추가
            if ($remove_whitespace == 'Y') {
                // 연속된 공백 제거
                $body = preg_replace('/\s+/', ' ', $body);
                // 문단 앞뒤 공백 제거
                $body = trim($body);
            }

            $output = "";
            $style = sprintf('margin: %spx; padding: %spx; background-color: #%s;', $margin, $padding, $bg_color);
            if ($linebreak == 'N') $style = "white-space: nowrap; overflow: auto; $style";
            if ($lineheight) $style = "line-height: $lineheight; $style";
            if ($font) $style = "font-family: $font; $style";

            // 기존의 border 스타일 설정 코드 유지
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

            // 기존의 폴더 기능 코드 유지
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

                $style .= "display:none;";

                $folder_margin = sprintf("%spx %spx %spx %spx", $margin, $margin, 10, $margin);
                $output .= sprintf('<div id="folder_open_%s" style="margin: %s; display: block;"><a class="%s" href="#" onclick="zbxe_folder_open(\'%s\');return false;">%s</a></div>', $folder_id, $folder_margin, $class, $folder_id, $folder_opener);
                $output .= sprintf('<div id="folder_close_%s" style="margin: %s; display: none;"><a class="%s" href="#" onclick="zbxe_folder_close(\'%s\');return false;">%s</a></div>', $folder_id, $folder_margin, $class, $folder_id, $folder_closer);

                $output .= sprintf('<div style="%s" id="folder_%s">%s</div>', $style, $folder_id, $body);
            } else {
                $output .= sprintf('<div style="%s">%s</div>', $style, $body);
            }
            return $output;
        }
    }
?>
