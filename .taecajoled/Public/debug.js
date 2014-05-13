{
  ___.loadModule({
      'instantiate': function (___, IMPORTS___) {
        var dis___ = IMPORTS___;
        var moduleResult___, x0___, x1___, x2___, x3___, x4___, x5___, x6___,
        x7___, x8___, x9___, x10___, x11___;
        moduleResult___ = ___.NO_RESULT;
        IMPORTS___.w___('DOM', (x0___ = IMPORTS___.KISSY_v___?
            IMPORTS___.KISSY: ___.ri(IMPORTS___, 'KISSY'), x0___.DOM_v___?
            x0___.DOM: x0___.v___('DOM'))), IMPORTS___.w___('Event', (x1___ =
            IMPORTS___.KISSY_v___? IMPORTS___.KISSY: ___.ri(IMPORTS___,
              'KISSY'), x1___.Event_v___? x1___.Event: x1___.v___('Event')));
        IMPORTS___.w___('btn_debug', (x2___ = IMPORTS___.DOM_v___?
            IMPORTS___.DOM: ___.ri(IMPORTS___, 'DOM'), x2___.query_m___?
            x2___.query('#think_page_trace_open'): x2___.m___('query', [
                '#think_page_trace_open' ]))[ +0 ]);
        x4___ = (x3___ = IMPORTS___.KISSY_v___? IMPORTS___.KISSY:
          ___.ri(IMPORTS___, 'KISSY'), x3___.Event_v___? x3___.Event:
          x3___.v___('Event')), x5___ = IMPORTS___.btn_debug_v___?
          IMPORTS___.btn_debug: ___.ri(IMPORTS___, 'btn_debug'), x6___ =
          ___.f(function (e) {
            var debug_panel, x0___, x1___, debug_tab, x2___, x3___, x4___,
            x5___, x6___, x7___, x8___;
            debug_panel = (x1___ = (x0___ = IMPORTS___.KISSY_v___?
                IMPORTS___.KISSY: ___.ri(IMPORTS___, 'KISSY'), x0___.DOM_v___?
                x0___.DOM: x0___.v___('DOM')), x1___.query_m___?
              x1___.query('#think_page_trace'): x1___.m___('query', [
                  '#think_page_trace' ]))[ +0 ];
            debug_tab = (x3___ = (x2___ = IMPORTS___.KISSY_v___?
                IMPORTS___.KISSY: ___.ri(IMPORTS___, 'KISSY'), x2___.DOM_v___?
                x2___.DOM: x2___.v___('DOM')), x3___.query_m___?
              x3___.query('#think_page_trace_tab'): x3___.m___('query', [
                  '#think_page_trace_tab' ]))[ +0 ];
            if ((x4___ = debug_tab.style_v___? debug_tab.style:
                debug_tab.v___('style'), x4___.display_v___? x4___.display:
                x4___.v___('display')) == 'block') {
              x5___ = debug_tab.style_v___? debug_tab.style:
              debug_tab.v___('style'), x5___.display_w___ === x5___?
                (x5___.display = 'none'): x5___.w___('display', 'none');
              x6___ = debug_panel.style_v___? debug_panel.style:
              debug_panel.v___('style'), x6___.display_w___ === x6___?
                (x6___.display = 'none'): x6___.w___('display', 'none');
            } else {
              x7___ = debug_tab.style_v___? debug_tab.style:
              debug_tab.v___('style'), x7___.display_w___ === x7___?
                (x7___.display = 'block'): x7___.w___('display', 'block');
              x8___ = debug_panel.style_v___? debug_panel.style:
              debug_panel.v___('style'), x8___.display_w___ === x8___?
                (x8___.display = 'block'): x8___.w___('display', 'block');
            }
          }), x4___.on_m___? x4___.on(x5___, 'click', x6___): x4___.m___('on',
          [ x5___, 'click', x6___ ]);
        IMPORTS___.w___('debug_title', (x7___ = IMPORTS___.DOM_v___?
            IMPORTS___.DOM: ___.ri(IMPORTS___, 'DOM'), x7___.query_m___?
            x7___.query('.debug_title'): x7___.m___('query', [ '.debug_title' ]
            )));
        IMPORTS___.w___('debug_info', (x8___ = IMPORTS___.DOM_v___?
            IMPORTS___.DOM: ___.ri(IMPORTS___, 'DOM'), x8___.query_m___?
            x8___.query('.debug_info'): x8___.m___('query', [ '.debug_info' ]))
        );
        moduleResult___ = (x9___ = IMPORTS___.Event_v___? IMPORTS___.Event:
          ___.ri(IMPORTS___, 'Event'), x10___ = IMPORTS___.debug_title_v___?
          IMPORTS___.debug_title: ___.ri(IMPORTS___, 'debug_title'), x11___ =
          ___.f(function (e) {
              var dis___ = this && this.___? void 0: this;
              var i, x0___, x1___, x2___, x3___, x4___, x5___, x6___, x7___,
              x8___, x9___;
              i = 0;
              {
                x2___ = Object(IMPORTS___.debug_title_v___?
                  IMPORTS___.debug_title: ___.ri(IMPORTS___, 'debug_title'))
                  .e___();
                for (x1___ in x2___) {
                  if (typeof x1___ === 'number' || '' + +x1___ === x1___) { i =
                      x1___; } else {
                    if (/^NUM___/.test(x1___) && /__$/.test(x1___)) { continue;
                    }
                    x0___ = x1___.match(/([\s\S]*)_e___$/);
                    if (!x0___ || !x2___[ x1___ ]) { continue; }
                    i = x0___[ 1 ];
                  }
                  {
                    x4___ = (x3___ = (IMPORTS___.debug_info_v___?
                        IMPORTS___.debug_info: ___.ri(IMPORTS___, 'debug_info')
                      ).v___(i), x3___.style_v___? x3___.style:
                      x3___.v___('style')), x4___.display_w___ === x4___?
                      (x4___.display = 'none'): x4___.w___('display', 'none');
                  }
                }
              }
              {
                x7___ = Object(IMPORTS___.debug_title_v___?
                  IMPORTS___.debug_title: ___.ri(IMPORTS___, 'debug_title'))
                  .e___();
                for (x6___ in x7___) {
                  if (typeof x6___ === 'number' || '' + +x6___ === x6___) { i =
                      x6___; } else {
                    if (/^NUM___/.test(x6___) && /__$/.test(x6___)) { continue;
                    }
                    x5___ = x6___.match(/([\s\S]*)_e___$/);
                    if (!x5___ || !x7___[ x6___ ]) { continue; }
                    i = x5___[ 1 ];
                  }
                  {
                    if ((IMPORTS___.debug_title_v___? IMPORTS___.debug_title:
                        ___.ri(IMPORTS___, 'debug_title')).v___(i) == dis___) {
                      break; }
                  }
                }
              }
              x9___ = (x8___ = (IMPORTS___.debug_info_v___?
                  IMPORTS___.debug_info: ___.ri(IMPORTS___, 'debug_info'))
                .v___(i), x8___.style_v___? x8___.style: x8___.v___('style')),
              x9___.display_w___ === x9___? (x9___.display = 'block'):
              x9___.w___('display', 'block');
            }), x9___.on_m___? x9___.on(x10___, 'click', x11___):
          x9___.m___('on', [ x10___, 'click', x11___ ]));
        return moduleResult___;
      },
      'cajolerName': 'com.google.caja',
      'cajolerVersion': '<unknown>',
      'cajoledDate': 1399949594321
});
}