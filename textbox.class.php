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

			Context::set('title', $this->getTitle());
			Context::set("tpl_path", $tpl_path);

			$oTemplate = TemplateHandler::getInstance();
			return $oTemplate->compile($tpl_path, $tpl_file);
		}

		/**
		 * @brief 에디터 컴포넌트가 별도의 고유 코드를 이용한다면 그 코드를 html로 변경하여 주는 method
		 **/

		private function getTitle(): string {
			$args = new stdClass();
			$args->component_name = 'textbox';
			$output = executeQueryArray('editor.getComponentExtraVars', $args);
			if(!$output->toBool() || !$output->data) return '';

			$extra_vars = unserialize($output->data[0]->extra_vars);
			return $extra_vars->title ?? '';
		}

		public function transHTML($xml_obj): string {
			$use_folder = $xml_obj->attrs->use_folder ?? 'N';
			$folder_opener = $xml_obj->attrs->folder_opener ?? 'more...';
			$folder_closer = $xml_obj->attrs->folder_closer ?? 'close...';
			$bold = $xml_obj->attrs->bold ?? 'N';
			$color = $xml_obj->attrs->color ?? 'blue';
			$text_color = $xml_obj->attrs->text_color ?? '000000';
			$font = $xml_obj->attrs->font ?? 'Consolas, Courier New, sans-serif';
			$lineheight = $xml_obj->attrs->lineheight ?? '';
			$linebreak = $xml_obj->attrs->linebreak ?? 'Y';
			$margin = $xml_obj->attrs->margin ?? '5';
			$padding = $xml_obj->attrs->padding ?? '10';
			$border_style = $xml_obj->attrs->border_style ?? '';
			$border_thickness = $xml_obj->attrs->border_thickness ?? '1';
			$border_color = $xml_obj->attrs->border_color ?? '999999';
			$bg_color = $xml_obj->attrs->bg_color ?? 'F7F7F6';
			$remove_whitespace = $xml_obj->attrs->remove_whitespace ?? 'N';
			$title = $xml_obj->attrs->title ?? $this->getTitle();
			$body = $xml_obj->body ?? '';

			// 공백 처리 기능 적용
			if ($remove_whitespace == 'Y') {
				$body = $this->processWhitespace($body);
			} else {
				$body = $this->normalizeNewlines($body);
			}

			$output = "";
			$style = sprintf('margin: %spx; margin-top: 26px; padding: %spx; background-color: #%s;', $margin, $padding, $bg_color);
			
			// pre 태그 기본 스타일 (linebreak 설정에 관계없이 공통 적용)
			$pre_style = 'margin: 0; padding: 0; white-space: pre-wrap; word-wrap: break-word; overflow: visible;';
			if ($text_color) $pre_style .= sprintf('color: #%s;', $text_color);

			// linebreak 설정에 따른 추가 스타일
			if ($linebreak == 'N') $pre_style = 'margin: 0; padding: 0; white-space: pre; overflow: auto;';

			if ($lineheight) $style .= "line-height: $lineheight;";
			if ($font) $style .= "font-family: $font;";
			
			switch($border_style) {
				case "solid": $style .= "border: {$border_thickness}px solid #{$border_color};"; break;
				case "dotted": $style .= "border: {$border_thickness}px dotted #{$border_color};"; break;
				case "left_solid": $style .= "border-left: {$border_thickness}px solid #{$border_color};"; break;
				case "left_dotted": $style .= "border-left: {$border_thickness}px dotted #{$border_color};"; break;
			}

			// 각 박스에 고유 ID 부여
			$box_id = 'textbox_' . random_int(1000000, 9999999);

			// 복사 버튼 스크립트 수정 (제목 제외 처리)
			$copy_button_script = '
			<script>
			function copytextboxContent(boxId) {
				var box = document.getElementById(boxId);
				var preElement = box.getElementsByTagName("pre")[0];
				var content = preElement.textContent || preElement.innerText;
				
				// 제목과 복사하기 버튼 텍스트 제거
				content = content.trim();

				var textarea = document.createElement("textarea");
				textarea.value = content;
				document.body.appendChild(textarea);
				
				textarea.select();
				document.execCommand("copy");
				
				document.body.removeChild(textarea);
				
				alert("내용이 클립보드에 복사되었습니다.");
			}
			</script>';

			// 타이틀 / 복사 버튼 스타일
			$title_style = 'position: absolute; top: -26px; left: 0; font-weight: bold; padding: 1px 1px 5px 5px; white-space: pre; overflow-x: auto; overflow-y: hidden; width:72%; height: 25px;';
			$button_style = 'position: absolute; top: -26px; right: 0px; padding: 3px 8px; background-color: #F7F7F6; border: 1px solid #999; border-radius: 3px; cursor: pointer; font-size: 12px; color:#444; width:70px;';

			// 스크롤바 스타일 (제목용)
			$scrollbar_style = '
			<style>
				.title-scroll::-webkit-scrollbar { 
					height: 3px !important; 
				}
				.title-scroll::-webkit-scrollbar-thumb { 
					border:3px solid gold;' . $border_color . '; 
				}
			</style>';

			static $script_added = false;
			if (!$script_added) {
				$output .= $copy_button_script;
				$output .= $scrollbar_style;
				$script_added = true;
			}

			// XML/HTML 내용인지 확인
			$is_xml = preg_match('/<\?xml|<var|<title|<description/i', $body);
			$content_class = $is_xml ? 'textbox-xml' : '';

			if($use_folder == "Y") {
				$folder_id = random_int(1000000, 9999999);
				$folder_opener = str_replace("&amp;","&",$folder_opener);
				$folder_closer = str_replace("&amp;","&",$folder_closer);

				$class = $bold == "Y" ? "bold" : "";
				switch($color) {
					case "red": $class .= " editor_red_text"; break;
					case "yellow": $class .= " editor_yellow_text"; break;
					case "green": $class .= " editor_green_text"; break;
					default: $class .= " editor_blue_text"; break;
				}

				$folder_style = $style . "position: relative;";
				$folder_margin = sprintf("%spx %spx %spx %spx", $margin, $margin, 10, $margin);
				
				$output .= sprintf('<div id="folder_open_%s" style="margin: %s; display: block;"><a class="%s" href="#" onclick="zbxe_folder_open(\'%s\');return false;">%s</a></div>', 
					$folder_id, $folder_margin, $class, $folder_id, $folder_opener);
				
				$output .= sprintf('<div id="folder_close_%s" style="margin: %s; display: none;"><a class="%s" href="#" onclick="zbxe_folder_close(\'%s\');return false;">%s</a></div>', 
					$folder_id, $folder_margin, $class, $folder_id, $folder_closer);

				$output .= sprintf('<div style="%s display:none;" id="folder_%s" data-source="%s">
					<div class="title-scroll" style="%s">%s</div>
					<pre class="%s" style="%s">%s</pre>
					<button onclick="copytextboxContent(\'folder_%s\')" style="%s">복사하기</button>
				</div>', 
					$folder_style, $folder_id, htmlspecialchars($body, ENT_QUOTES),
					$title_style, $title,
					$content_class, $pre_style, $body, $folder_id, $button_style);
			} else {
				$output .= sprintf('<div style="%s position: relative;" id="%s" data-source="%s">
					<div class="title-scroll" style="%s">%s</div>
					<pre class="%s" style="%s">%s</pre>
					<button onclick="copytextboxContent(\'%s\')" style="%s">복사하기</button>
				</div>', 
					$style, $box_id, htmlspecialchars($body, ENT_QUOTES),
					$title_style, $title,
					$content_class, $pre_style, $body, $box_id, $button_style);
			}
			return $output;
		}

		/**
		 * @brief 코드 하이라이터 스타일의 공백 처리
		 **/
		private function processWhitespace(string $content): string {
			// 1. 모든 HTML 태그 제거 (br과 span 태그는 보존)
			$content = strip_tags($content, '<br><span>');
			
			// 2. <br> 태그와 그 뒤의 개행 문자 제거
			$content = preg_replace("/(<br\s*\/?>)(\n|\r)*/i", "\n", $content);
			
			// 3. span 태그 보존하고 다른 HTML 태그 제거 (이전에 strip_tags로 다른 태그는 이미 제거됨)
			// $content = strip_tags($content); - 이 줄을 제거함
			
			// 4. &nbsp;를 일반 공백으로 변환
			$content = str_replace('&nbsp;', ' ', $content);
			
			// 5. 가장 작은 선행 공백 찾기
			$minIndent = null;
			$lines = explode("\n", $content);
			
			foreach ($lines as $line) {
				if (trim($line) === '') continue;
				
				// span 태그를 제외한 실제 내용의 들여쓰기 확인
				$text_only = preg_replace('/<span[^>]*>|<\/span>/', '', $line);
				
				preg_match('/^(\s+)/', $text_only, $matches);
				$leadingSpaces = isset($matches[1]) ? strlen($matches[1]) : 0;
				
				if ($minIndent === null || $leadingSpaces < $minIndent) {
					$minIndent = $leadingSpaces;
				}
			}
			
			// 6. 모든 줄에서 최소 공백만큼 제거 (span 태그 구조 유지)
			if ($minIndent > 0) {
				$processed_lines = [];
				foreach ($lines as $line) {
					if (trim($line) === '') {
						$processed_lines[] = $line;
						continue;
					}
					
					// span 태그 기준으로 분리하여 각 부분 처리
					$parts = preg_split('/(<span[^>]*>|<\/span>)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
					$result = '';
					$in_tag = false;
					
					foreach ($parts as $part) {
						if (preg_match('/^<span[^>]*>$/', $part)) {
							$result .= $part;
							$in_tag = true;
						} else if ($part === '</span>') {
							$result .= $part;
							$in_tag = false;
						} else if (!$in_tag && !empty($part)) {
							// 실제 텍스트 부분에서만 들여쓰기 제거
							$result .= preg_replace('/^' . str_repeat(' ', $minIndent) . '/', '', $part, 1);
						} else {
							$result .= $part;
						}
					}
					
					$processed_lines[] = $result;
				}
				$content = implode("\n", $processed_lines);
			}
			
			return $content;
		}
		
		/**
		 * @brief remove_whitespace가 'N'일 때 줄바꿈 정규화
		 **/
		private function normalizeNewlines(string $content): string {
			// 1. <br>과 <span> 태그만 보존하고 다른 모든 HTML 태그 제거
			$content = strip_tags($content, '<br><span>');
			
			// 2. <br> 태그를 개행 문자로 변환
			$content = preg_replace("/(<br\s*\/?>)/i", "\n", $content);
			
			// 3. <span> 태그는 보존하고 다른 남은 HTML 태그는 모두 제거
			$content = strip_tags($content, '<span>');
			
			// 4. &nbsp;를 일반 공백으로 변환
			$content = str_replace('&nbsp;', ' ', $content);
			
			// 5. 여러 개의 연속된 개행 문자를 하나로 통일
			$content = preg_replace('/\n\s*\n/', "\n", $content);
			
			return $content;
		}
	}
?>