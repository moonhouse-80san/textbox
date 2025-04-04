<?php
    /**
     * @class  textbox
     * @author mooo (hhyoon@kldp.org)
     * @brief  에디터에서 PRE 문단 기능 제공. 코드 하이라이터 스타일의 공백 처리 및 XML 포맷팅 지원 추가
     **/

    class textbox extends EditorHandler {

        // editor_sequence 는 에디터에서 필수로 달고 다녀야 함....
        protected int $editor_sequence = 0;
        protected string $component_path = '';

        /**
         * @brief editor_sequence과 컴포넌트의 경로를 받음
         **/
        public function __construct(int $editor_sequence, string $component_path) {
            $this->editor_sequence = $editor_sequence;
            $this->component_path = $component_path;
        }

        /**
         * @brief popup window요청시 popup window에 출력할 내용을 추가하면 된다
         **/
        public function getPopupContent(): string {
            // 템플릿을 미리 컴파일해서 컴파일된 소스를 return
            $tpl_path = $this->component_path.'tpl';
            $tpl_file = 'popup.html';

            Context::set("tpl_path", $tpl_path);

            $oTemplate = TemplateHandler::getInstance();
            return $oTemplate->compile($tpl_path, $tpl_file);
        }

        /**
         * @brief 에디터 컴포넌트가 별도의 고유 코드를 이용한다면 그 코드를 html로 변경하여 주는 method
         **/
		public function transHTML($xml_obj): string {
			$use_folder = $xml_obj->attrs->use_folder;
			$folder_opener = $xml_obj->attrs->folder_opener;
			if(!$folder_opener) $folder_opener = "more...";
			$folder_closer = $xml_obj->attrs->folder_closer;
			if(!$folder_closer) $folder_closer= "close...";
			$bold = $xml_obj->attrs->bold;
			$color = $xml_obj->attrs->color;
			$text_color = $xml_obj->attrs->text_color;
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

			// 공백 처리 기능 적용
			if ($remove_whitespace == 'Y') {
				$body = $this->processWhitespace($body);
			} else {
				// 'N'일 때도 기본적인 줄바꿈 처리를 해줍니다
				$body = $this->normalizeNewlines($body);
			}

			$output = "";
			$style = sprintf('margin: %spx; margin-top: 26px; padding: %spx; background-color: #%s;', $margin, $padding, $bg_color);
			if ($linebreak == 'N') $style = "white-space: nowrap; overflow: auto; $style";
			if ($lineheight) $style = "line-height: $lineheight; $style";
			if ($font) $style = "font-family: $font; $style";
			
			// 텍스트 색상 스타일 (pre 태그에 적용)
			$pre_style = '';
			if ($text_color) {
				$pre_style = sprintf('color: #%s;', $text_color);
			}

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
			$box_id = 'textbox_' . random_int(1000000, 9999999);

			// 복사 버튼을 위한 JavaScript 함수 추가
			$copy_button_script = '
			<script>
			function copytextboxContent(boxId) {
				var box = document.getElementById(boxId);
				var content = box.textContent || box.innerText;
				
				content = content.replace("복사하기", "").trim();

				var sourceCode = box.getAttribute("data-source");
				
				var textarea = document.createElement("textarea");
				textarea.value = content;
				document.body.appendChild(textarea);
				
				textarea.select();
				document.execCommand("copy");
				
				document.body.removeChild(textarea);
				
				alert("내용이 클립보드에 복사되었습니다.");
			}
			</script>';
			
			static $script_added = false;
			if (!$script_added) {
				$output .= $copy_button_script;
				$script_added = true;
			}

			// 복사 버튼 스타일
			$button_style = 'position: absolute; top: -26px; right: 0px; padding: 3px 8px; ' .
						   'background-color: #F7F7F6; border: 1px solid #999; ' .
						   'border-radius: 3px; cursor: pointer; font-size: 12px; color:#444;';

			// XML/HTML 내용인지 확인
			$is_xml = preg_match('/<\?xml|<var|<title|<description/i', $body);
			$content_class = $is_xml ? 'textbox-xml' : '';

			if($use_folder == "Y") {
				$folder_id = random_int(1000000, 9999999);

				$folder_opener = str_replace("&amp;","&",$folder_opener);
				$folder_closer = str_replace("&amp;","&",$folder_closer);

				$class = "";
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

				$output .= sprintf('<div style="%s" id="folder_%s" data-source="%s"><pre class="%s" style="%s">%s</pre><button onclick="copytextboxContent(\'folder_%s\')" style="%s">복사하기</button></div>', 
					$style, 
					$folder_id, 
					htmlspecialchars($body, ENT_QUOTES), 
					$content_class,
					$pre_style, // pre 태그에 텍스트 색상 적용
					$body, 
					$folder_id, 
					$button_style
				);
			} else {
				$output .= sprintf('<div style="%s position: relative;" id="%s" data-source="%s"><pre class="%s" style="%s">%s</pre><button onclick="copytextboxContent(\'%s\')" style="%s">복사하기</button></div>', 
					$style, 
					$box_id, 
					htmlspecialchars($body, ENT_QUOTES), 
					$content_class,
					$pre_style, // pre 태그에 텍스트 색상 적용
					$body, 
					$box_id, 
					$button_style
				);
			}
			return $output;
		}

        /**
         * @brief 코드 하이라이터 스타일의 공백 처리
         **/
        private function processWhitespace(string $content): string {
            // 1. 모든 HTML 태그 제거 (br 태그는 보존)
            $content = strip_tags($content, '<br>');
            
            // 2. <br> 태그와 그 뒤의 개행 문자 제거
            $content = preg_replace("/(<br\s*\/?>)(\n|\r)*/i", "\n", $content);
            
            // 3. 남은 HTML 태그 모두 제거
            $content = strip_tags($content);
            
            // 4. &nbsp;를 일반 공백으로 변환
            $content = str_replace('&nbsp;', ' ', $content);
            
            // 5. 가장 작은 선행 공백 찾기
            $minIndent = null;
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                
                preg_match('/^(\s+)/', $line, $matches);
                $leadingSpaces = isset($matches[1]) ? strlen($matches[1]) : 0;
                
                if ($minIndent === null || $leadingSpaces < $minIndent) {
                    $minIndent = $leadingSpaces;
                }
            }
            
            // 6. 모든 줄에서 최소 공백만큼 제거
            if ($minIndent > 0) {
                $pattern = '/^' . str_repeat(' ', $minIndent) . '/';
                foreach ($lines as &$line) {
                    $line = preg_replace($pattern, '', $line);
                }
                $content = implode("\n", $lines);
            }
            
            return $content;
        }
        
        /**
         * @brief remove_whitespace가 'N'일 때 줄바꿈 정규화
         **/
        private function normalizeNewlines(string $content): string {
            // 1. 모든 HTML 태그 제거 (br 태그는 보존)
            $content = strip_tags($content, '<br>');
            
            // 2. <br> 태그를 개행 문자로 변환
            $content = preg_replace("/(<br\s*\/?>)/i", "\n", $content);
            
            // 3. 남은 HTML 태그 모두 제거
            $content = strip_tags($content);
            
            // 4. &nbsp;를 일반 공백으로 변환
            $content = str_replace('&nbsp;', ' ', $content);
            
            // 5. 여러 개의 연속된 개행 문자를 하나로 통일
            $content = preg_replace('/\n\s*\n/', "\n", $content);
            
            return $content;
        }
    }
?>