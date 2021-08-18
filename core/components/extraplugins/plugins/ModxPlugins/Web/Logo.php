<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 17.08.2021
 * Time: 8:58
 */

namespace modxPlugins\Web;

use modSystemEvent;
use ModxPlugins\Extra;

class Logo extends Extra
{

    /**
     * Замена логотипа
     * @param modSystemEvent $event
     * @param array $scriptProperties
     */
    public function OnHandleRequest(modSystemEvent $event, $scriptProperties = array())
    {
        if($this->modx->context->key === 'mgr') {
            $logo = 'iVBORw0KGgoAAAANSUhEUgAAAFAAAABDCAYAAAALU4KYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QzM0RTQ0RjlFQjMyMTFFNEI1ODFFRDVBNEEyNTUxMzUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QzM0RTQ0RkFFQjMyMTFFNEI1ODFFRDVBNEEyNTUxMzUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpDMzRFNDRGN0VCMzIxMUU0QjU4MUVENUE0QTI1NTEzNSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpDMzRFNDRGOEVCMzIxMUU0QjU4MUVENUE0QTI1NTEzNSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pm535L8AAAnmSURBVHja7JsLUJTX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';
            $this->modx->regClientCSS('
				<style>
				#modx-navbar #modx-home-dashboard {
				  background-image: url(data:image/png;base64,'.$logo.');
				  -webkit-background-size: 80px 67px;
				  background-size: 80px 67px;
				  width: 95px;
				}
				</style>');
        }

    }

}
