(window.__wcAdmin_webpackJsonp=window.__wcAdmin_webpackJsonp||[]).push([[11],{772:function(e,t,n){"use strict";var c=n(778),a=["a","b","em","i","strong","p","br"],r=["target","href","rel","name","download"];t.a=function(e){return{__html:Object(c.sanitize)(e,{ALLOWED_TAGS:a,ALLOWED_ATTR:r})}}},773:function(e,t,n){"use strict";n.d(t,"a",(function(){return C})),n.d(t,"b",(function(){return k}));var c=n(17),a=n.n(c),r=n(15),o=n.n(r),i=n(18),s=n.n(i),l=n(19),m=n.n(l),u=n(9),d=n.n(u),b=n(0),f=n(4),p=n.n(f),v=n(104),_=n.n(v),h=n(16),y=n.n(h),O=n(1),w=n.n(O),g=n(77),j=n(72),E=(n(777),n(2));function N(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,c=d()(e);if(t){var a=d()(this).constructor;n=Reflect.construct(c,arguments,a)}else n=c.apply(this,arguments);return m()(this,n)}}var R=function(e){s()(n,e);var t=N(n);function n(){return a()(this,n),t.apply(this,arguments)}return o()(n,[{key:"render",value:function(){var e=this.props,t=e.className,n=e.hasAction,c=e.hasDate,a=e.hasSubtitle,r=e.lines,o=p()("woocommerce-activity-card is-loading",t);return Object(b.createElement)("div",{className:o,"aria-hidden":!0},Object(b.createElement)("span",{className:"woocommerce-activity-card__icon"},Object(b.createElement)("span",{className:"is-placeholder"})),Object(b.createElement)("div",{className:"woocommerce-activity-card__header"},Object(b.createElement)("div",{className:"woocommerce-activity-card__title is-placeholder"}),a&&Object(b.createElement)("div",{className:"woocommerce-activity-card__subtitle is-placeholder"}),c&&Object(b.createElement)("div",{className:"woocommerce-activity-card__date"},Object(b.createElement)("span",{className:"is-placeholder"}))),Object(b.createElement)("div",{className:"woocommerce-activity-card__body"},Object(E.range)(r).map((function(e){return Object(b.createElement)("span",{className:"is-placeholder",key:e})}))),n&&Object(b.createElement)("div",{className:"woocommerce-activity-card__actions"},Object(b.createElement)("span",{className:"is-placeholder"})))}}]),n}(b.Component);R.propTypes={className:w.a.string,hasAction:w.a.bool,hasDate:w.a.bool,hasSubtitle:w.a.bool,lines:w.a.number},R.defaultProps={hasAction:!1,hasDate:!1,hasSubtitle:!1,lines:1};var k=R;function D(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,c=d()(e);if(t){var a=d()(this).constructor;n=Reflect.construct(c,arguments,a)}else n=c.apply(this,arguments);return m()(this,n)}}var C=function(e){s()(n,e);var t=D(n);function n(){return a()(this,n),t.apply(this,arguments)}return o()(n,[{key:"getCard",value:function(){var e=this.props,t=e.actions,n=e.className,c=e.children,a=e.date,r=e.icon,o=e.subtitle,i=e.title,s=e.unread,l=p()("woocommerce-activity-card",n),m=Array.isArray(t)?t:[t];return Object(b.createElement)("section",{className:l},s&&Object(b.createElement)("span",{className:"woocommerce-activity-card__unread"}),r&&Object(b.createElement)("span",{className:"woocommerce-activity-card__icon","aria-hidden":!0},r),i&&Object(b.createElement)("header",{className:"woocommerce-activity-card__header"},Object(b.createElement)(g.H,{className:"woocommerce-activity-card__title"},i),o&&Object(b.createElement)("div",{className:"woocommerce-activity-card__subtitle"},o),a&&Object(b.createElement)("span",{className:"woocommerce-activity-card__date"},y.a.utc(a).fromNow())),c&&Object(b.createElement)(g.Section,{className:"woocommerce-activity-card__body"},c),t&&Object(b.createElement)("footer",{className:"woocommerce-activity-card__actions"},m.map((function(e,t){return Object(b.cloneElement)(e,{key:t})}))))}},{key:"render",value:function(){var e=this.props.onClick;return e?Object(b.createElement)(j.a,{className:"woocommerce-activity-card__button",onClick:e},this.getCard()):this.getCard()}}]),n}(b.Component);C.propTypes={actions:w.a.oneOfType([w.a.arrayOf(w.a.element),w.a.element]),onClick:w.a.func,className:w.a.string,children:w.a.node,date:w.a.string,icon:w.a.node,subtitle:w.a.node,title:w.a.oneOfType([w.a.string,w.a.node]),unread:w.a.bool},C.defaultProps={icon:Object(b.createElement)(_.a,{icon:"notice-outline",size:48}),unread:!1}},774:function(e,t,n){"use strict";var c=n(17),a=n.n(c),r=n(15),o=n.n(r),i=n(18),s=n.n(i),l=n(19),m=n.n(l),u=n(9),d=n.n(u),b=n(0),f=n(4),p=n.n(f),v=n(1),_=n.n(v),h=n(190),y=n(77);n(775);function O(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,c=d()(e);if(t){var a=d()(this).constructor;n=Reflect.construct(c,arguments,a)}else n=c.apply(this,arguments);return m()(this,n)}}var w=function(e){s()(n,e);var t=O(n);function n(){return a()(this,n),t.apply(this,arguments)}return o()(n,[{key:"render",value:function(){var e=this.props,t=e.className,n=e.menu,c=e.subtitle,a=e.title,r=e.unreadMessages,o=p()({"woocommerce-layout__inbox-panel-header":c,"woocommerce-layout__activity-panel-header":!c},t),i=r||0;return Object(b.createElement)("div",{className:o},Object(b.createElement)("div",{className:"woocommerce-layout__inbox-title"},Object(b.createElement)(h.a,{variant:"title.small"},a),Object(b.createElement)(h.a,{variant:"button"},i>0&&Object(b.createElement)("span",{className:"woocommerce-layout__inbox-badge"},r))),Object(b.createElement)("div",{className:"woocommerce-layout__inbox-subtitle"},c&&Object(b.createElement)(h.a,{variant:"body.small"},c)),n&&Object(b.createElement)("div",{className:"woocommerce-layout__activity-panel-header-menu"},n))}}]),n}(b.Component);w.propTypes={className:_.a.string,unreadMessages:_.a.number,title:_.a.string.isRequired,subtitle:_.a.string,menu:_.a.shape({type:_.a.oneOf([y.EllipsisMenu])})},t.a=w},775:function(e,t,n){},777:function(e,t,n){},784:function(e,t,n){},798:function(e,t,n){"use strict";n.r(t);var c=n(62),a=n.n(c),r=n(0),o=n(3),i=n(277),s=n(77),l=n(35),m=n(20),u=n(560),d=n(559),b=n(773),f=n(17),p=n.n(f),v=n(15),_=n.n(v),h=n(18),y=n.n(h),O=n(19),w=n.n(O),g=n(9),j=n.n(g),E=n(1),N=n.n(E);function R(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,c=j()(e);if(t){var a=j()(this).constructor;n=Reflect.construct(c,arguments,a)}else n=c.apply(this,arguments);return w()(this,n)}}var k=function(e){y()(n,e);var t=R(n);function n(){return p()(this,n),t.apply(this,arguments)}return _()(n,[{key:"render",value:function(){var e=this.props.className;return Object(r.createElement)("div",{className:"woocommerce-inbox-message is-placeholder ".concat(e),"aria-hidden":!0},Object(r.createElement)("div",{className:"woocommerce-inbox-message__image"},Object(r.createElement)("div",{className:"banner-block"})),Object(r.createElement)("div",{className:"woocommerce-inbox-message__wrapper"},Object(r.createElement)("div",{className:"woocommerce-inbox-message__content"},Object(r.createElement)("div",{className:"woocommerce-inbox-message__date"},Object(r.createElement)("div",{className:"sixth-line"})),Object(r.createElement)("div",{className:"woocommerce-inbox-message__title"},Object(r.createElement)("div",{className:"line"}),Object(r.createElement)("div",{className:"line"})),Object(r.createElement)("div",{className:"woocommerce-inbox-message__text"},Object(r.createElement)("div",{className:"line"}),Object(r.createElement)("div",{className:"third-line"}))),Object(r.createElement)("div",{className:"woocommerce-inbox-message__actions"},Object(r.createElement)("div",{className:"fifth-line"}),Object(r.createElement)("div",{className:"fifth-line"}))))}}]),n}(r.Component);k.propTypes={className:N.a.string};var D=k,C=n(11),x=n.n(C),T=n(5),A=n.n(T),S=n(535),M=n(72),L=n(736),U=n(783),B=n.n(U),P=n(16),q=n.n(P),F=n(4),I=n.n(F),Y=n(64),z=n(36);function V(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,c=j()(e);if(t){var a=j()(this).constructor;n=Reflect.construct(c,arguments,a)}else n=c.apply(this,arguments);return w()(this,n)}}var W=function(e){y()(n,e);var t=V(n);function n(e){var c;return p()(this,n),(c=t.call(this,e)).state={inAction:!1},c.handleActionClick=c.handleActionClick.bind(x()(c)),c}return _()(n,[{key:"handleActionClick",value:function(e){var t=this.props,n=t.action,c=t.actionCallback,a=t.batchUpdateNotes,r=t.createNotice,i=t.noteId,s=t.triggerNoteAction,l=t.removeAllNotes,m=t.removeNote,u=t.onClick,d=t.updateNote,b=e.target.href||"",f=!0;b.length&&!b.startsWith(z.a)&&(e.preventDefault(),f=!1,window.open(b,"_blank")),n?this.setState({inAction:f},(function(){s(i,n.id),u&&u()})):(i?m(i).then((function(){r("success",Object(o.__)("Message dismissed.",'woocommerce'),{actions:[{label:Object(o.__)("Undo",'woocommerce'),onClick:function(){d(i,{is_deleted:0})}}]})})).catch((function(){r("error",Object(o.__)("Message could not be dismissed.",'woocommerce'))})):l().then((function(e){r("success",Object(o.__)("All messages dismissed.",'woocommerce'),{actions:[{label:Object(o.__)("Undo",'woocommerce'),onClick:function(){a(e.map((function(e){return e.id})),{is_deleted:0})}}]})})).catch((function(){r("error",Object(o.__)("Message could not be dismissed.",'woocommerce'))})),c(!0))}},{key:"render",value:function(){var e=this.props,t=e.action,n=e.dismiss,c=e.label;return Object(r.createElement)(M.a,{isSecondary:!0,isBusy:this.state.inAction,disabled:this.state.inAction,href:t&&t.url&&t.url.length?t.url:void 0,onClick:this.handleActionClick},n?c:t.label)}}]),n}(r.Component);W.propTypes={noteId:N.a.number,label:N.a.string,dismiss:N.a.bool,actionCallback:N.a.func,action:N.a.shape({id:N.a.number.isRequired,url:N.a.string,label:N.a.string.isRequired,primary:N.a.bool.isRequired}),onClick:N.a.func};var H=Object(i.a)(Object(m.withDispatch)((function(e){var t=e("core/notices").createNotice,n=e(l.NOTES_STORE_NAME),c=n.batchUpdateNotes,a=n.removeAllNotes,r=n.removeNote,o=n.updateNote;return{batchUpdateNotes:c,createNotice:t,removeAllNotes:a,removeNote:r,triggerNoteAction:n.triggerNoteAction,updateNote:o}})))(W),Q=n(772);n(784);function G(){var e,t="",n=(e=window.location.search)?e.substr(1).split("&").reduce((function(e,t){var n=t.split("="),c=n[0],a=decodeURIComponent(n[1]);return a=isNaN(Number(a))?a:Number(a),e[c]=a,e}),{}):{},c=n.page,a=n.path,r=n.post_type;if(c){var o="wc-admin"===c?"home_screen":c;t=a?a.replace(/\//g,"_").substring(1):o}else r&&(t=r);return t}function J(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,c=j()(e);if(t){var a=j()(this).constructor;n=Reflect.construct(c,arguments,a)}else n=c.apply(this,arguments);return w()(this,n)}}var Z=function(e){y()(n,e);var t=J(n);function n(e){var c;return p()(this,n),c=t.call(this,e),A()(x()(c),"onActionClicked",(function(e){e.actioned_text&&c.setState({clickedActionText:e.actioned_text})})),c.onVisible=c.onVisible.bind(x()(c)),c.hasBeenSeen=!1,c.state={isDismissModalOpen:!1,dismissType:null,clickedActionText:null},c.openDismissModal=c.openDismissModal.bind(x()(c)),c.closeDismissModal=c.closeDismissModal.bind(x()(c)),c.bodyNotificationRef=Object(r.createRef)(),c.screen=G(),c}return _()(n,[{key:"componentDidMount",value:function(){var e=this;this.bodyNotificationRef.current&&this.bodyNotificationRef.current.addEventListener("click",(function(t){return e.handleBodyClick(t,e.props)}))}},{key:"componentWillUnmount",value:function(){var e=this;this.bodyNotificationRef.current&&this.bodyNotificationRef.current.removeEventListener("click",(function(t){return e.handleBodyClick(t,e.props)}))}},{key:"handleBodyClick",value:function(e,t){var n=e.target.href;if(n){var c=t.note;Object(Y.recordEvent)("wcadmin_inbox_action_click",{note_name:c.name,note_title:c.title,note_content_inner_link:n})}}},{key:"onVisible",value:function(e){if(e&&!this.hasBeenSeen){var t=this.props.note;Object(Y.recordEvent)("inbox_note_view",{note_content:t.content,note_name:t.name,note_title:t.title,note_type:t.type,screen:this.screen}),this.hasBeenSeen=!0}}},{key:"openDismissModal",value:function(e,t){this.setState({isDismissModalOpen:!0,dismissType:e}),t()}},{key:"closeDismissModal",value:function(e){var t=this.state.dismissType,n=this.props.note,c="all"===t;Object(Y.recordEvent)("inbox_action_dismiss",{note_name:n.name,note_title:n.title,note_name_dismiss_all:c,note_name_dismiss_confirmation:e||!1,screen:this.screen}),this.setState({isDismissModalOpen:!1})}},{key:"handleBlur",value:function(e,t){var n=e.relatedTarget?e.relatedTarget:document.activeElement;!!n&&["woocommerce-admin-dismiss-notification","components-popover__content"].some((function(e){return n.className.includes(e)}))?e.preventDefault():t()}},{key:"renderDismissButton",value:function(){var e=this;return this.state.clickedActionText?null:Object(r.createElement)(S.a,{contentClassName:"woocommerce-admin-dismiss-dropdown",position:"bottom right",renderToggle:function(t){var n=t.onClose,c=t.onToggle;return Object(r.createElement)(M.a,{isTertiary:!0,onClick:c,onBlur:function(t){return e.handleBlur(t,n)}},Object(o.__)("Dismiss",'woocommerce'))},focusOnMount:!1,popoverProps:{noArrow:!0},renderContent:function(t){var n=t.onToggle;return Object(r.createElement)("ul",null,Object(r.createElement)("li",null,Object(r.createElement)(M.a,{className:"woocommerce-admin-dismiss-notification",onClick:function(){return e.openDismissModal("this",n)}},Object(o.__)("Dismiss this message",'woocommerce'))),Object(r.createElement)("li",null,Object(r.createElement)(M.a,{className:"woocommerce-admin-dismiss-notification",onClick:function(){return e.openDismissModal("all",n)}},Object(o.__)("Dismiss all messages",'woocommerce'))))}})}},{key:"getDismissConfirmationButton",value:function(){var e=this.props.note,t=this.state.dismissType;return Object(r.createElement)(H,{key:e.id,noteId:"all"===t?null:e.id,label:Object(o.__)("Yes, I'm sure",'woocommerce'),actionCallback:this.closeDismissModal,dismiss:!0,screen:this.screen})}},{key:"renderDismissConfirmationModal",value:function(){var e=this;return Object(r.createElement)(L.a,{title:Object(r.createElement)(r.Fragment,null,Object(o.__)("Are you sure?",'woocommerce')),onRequestClose:function(){return e.closeDismissModal()},className:"woocommerce-inbox-dismiss-confirmation_modal"},Object(r.createElement)("div",{className:"woocommerce-inbox-dismiss-confirmation_wrapper"},Object(r.createElement)("p",null,Object(o.__)("Dismissed messages cannot be viewed again",'woocommerce')),Object(r.createElement)("div",{className:"woocommerce-inbox-dismiss-confirmation_buttons"},Object(r.createElement)(M.a,{isSecondary:!0,onClick:function(){return e.closeDismissModal()}},Object(o.__)("Cancel",'woocommerce')),this.getDismissConfirmationButton())))}},{key:"renderActions",value:function(e){var t=this,n=e.actions,c=e.id,a=this.state.clickedActionText;return a||(n?Object(r.createElement)(r.Fragment,null,n.map((function(e,n){return Object(r.createElement)(H,{key:n,noteId:c,action:e,onClick:function(){return t.onActionClicked(e)}})}))):void 0)}},{key:"render",value:function(){var e=this.props,t=e.lastRead,n=e.note,c=this.state.isDismissModalOpen,a=n.content,o=n.date_created,i=n.date_created_gmt,l=n.image,m=n.is_deleted,u=n.layout,d=n.status,b=n.title;if(m)return null;var f=!t||!i||new Date(i+"Z").getTime()>t,p=o,v="plain"!==u&&""!==u,_=I()("woocommerce-inbox-message",u,{"message-is-unread":f&&"unactioned"===d});return Object(r.createElement)(B.a,{onChange:this.onVisible},Object(r.createElement)("section",{className:_},v&&Object(r.createElement)("div",{className:"woocommerce-inbox-message__image"},Object(r.createElement)("img",{src:l,alt:""})),Object(r.createElement)("div",{className:"woocommerce-inbox-message__wrapper"},Object(r.createElement)("div",{className:"woocommerce-inbox-message__content"},p&&Object(r.createElement)("span",{className:"woocommerce-inbox-message__date"},q.a.utc(p).fromNow()),Object(r.createElement)(s.H,{className:"woocommerce-inbox-message__title"},b),Object(r.createElement)(s.Section,{className:"woocommerce-inbox-message__text"},Object(r.createElement)("span",{dangerouslySetInnerHTML:Object(Q.a)(a),ref:this.bodyNotificationRef}))),Object(r.createElement)("div",{className:"woocommerce-inbox-message__actions"},this.renderActions(n),this.renderDismissButton())),c&&this.renderDismissConfirmationModal()))}}]),n}(r.Component);Z.propTypes={note:N.a.shape({id:N.a.number,status:N.a.string,title:N.a.string,content:N.a.string,date_created:N.a.string,date_created_gmt:N.a.string,actions:N.a.arrayOf(N.a.shape({id:N.a.number.isRequired,url:N.a.string,label:N.a.string.isRequired,primary:N.a.bool.isRequired})),layout:N.a.string,image:N.a.string,is_deleted:N.a.bool}),lastRead:N.a.number};var K=Z,X=n(446),$=function(e){var t=e.hasNotes,n=e.isBatchUpdating,c=e.lastRead,a=e.notes;if(!n){if(!t)return Object(r.createElement)(b.a,{className:"woocommerce-empty-activity-card",title:Object(o.__)("Your inbox is empty",'woocommerce'),icon:!1},Object(o.__)("As things begin to happen in your store your inbox will start to fill up. You'll see things like achievements, new feature announcements, extension recommendations and more!",'woocommerce'));var i=Object.keys(a).map((function(e){return a[e]}));return Object(r.createElement)(u.a,{role:"menu"},i.map((function(e){var t=e.id;return e.is_deleted?null:Object(r.createElement)(d.a,{key:t,timeout:500,classNames:"woocommerce-inbox-message"},Object(r.createElement)(K,{key:t,note:e,lastRead:c}))})))}};t.default=Object(i.a)(Object(m.withSelect)((function(e){var t=e(l.NOTES_STORE_NAME),n=t.getNotes,c=t.getNotesError,a=t.isResolving,r=t.isNotesRequesting,o={page:1,per_page:l.QUERY_DEFAULTS.pageSize,status:"unactioned",type:l.QUERY_DEFAULTS.noteTypes,orderby:"date",order:"desc",_fields:["id","name","title","content","type","status","actions","date_created","date_created_gmt","layout","image","is_deleted"]};return{notes:n(o),isError:Boolean(c("getNotes",[o])),isResolving:a("getNotes",[o]),isBatchUpdating:r("batchUpdateNotes")}})))((function(e){var t=e.isError,n=e.isResolving,c=e.isBatchUpdating,i=e.notes,m=Object(l.useUserPreferences)(),u=m.updateUserPreferences,d=a()(m,["updateUserPreferences"]).activity_panel_inbox_last_read;if(Object(r.useEffect)((function(){var e=Date.now();return function(){u({activity_panel_inbox_last_read:e})}}),[]),t){var b=Object(o.__)("There was an error getting your inbox. Please try again.",'woocommerce'),f=Object(o.__)("Reload",'woocommerce');return Object(r.createElement)(s.EmptyContent,{title:b,actionLabel:f,actionURL:null,actionCallback:function(){window.location.reload()}})}var p=Object(X.b)(i);return Object(r.createElement)(r.Fragment,null,Object(r.createElement)("div",{className:"woocommerce-homepage-notes-wrapper"},(n||c)&&Object(r.createElement)(s.Section,null,Object(r.createElement)(D,{className:"banner message-is-unread"})),Object(r.createElement)(s.Section,null,!n&&!c&&$({hasNotes:p,isBatchUpdating:c,lastRead:d,notes:i}))))}))},845:function(e,t,n){"use strict";n.r(t);var c=n(17),a=n.n(c),r=n(15),o=n.n(r),i=n(18),s=n.n(i),l=n(19),m=n.n(l),u=n(9),d=n.n(u),b=n(0),f=n(3),p=n(4),v=n.n(p),_=n(20),h=n(72),y=n(104),O=n.n(y),w=n(42),g=n.n(w),j=n(2),E=n(1),N=n.n(E),R=n(77),k=n(36),D=n(35),C=n(64),x=n(773),T=n(774),A=n(772);function S(e){var t=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}();return function(){var n,c=d()(e);if(t){var a=d()(this).constructor;n=Reflect.construct(c,arguments,a)}else n=c.apply(this,arguments);return m()(this,n)}}var M=function(e){s()(n,e);var t=S(n);function n(){var e;return a()(this,n),(e=t.call(this)).mountTime=(new Date).getTime(),e}return o()(n,[{key:"recordReviewEvent",value:function(e){Object(C.recordEvent)("activity_panel_reviews_".concat(e),{})}},{key:"renderReview",value:function(e,t){var n=this,c=t.lastRead,a=e&&e._embedded&&e._embedded.up&&e._embedded.up[0]||null;if(Object(j.isNull)(a))return null;var r=g()({mixedString:Object(f.sprintf)(Object(f.__)("{{productLink}}%s{{/productLink}} reviewed by {{authorLink}}%s{{/authorLink}}",'woocommerce'),a.name,e.reviewer),components:{productLink:Object(b.createElement)(R.Link,{href:a.permalink,onClick:function(){return n.recordReviewEvent("product")},type:"external"}),authorLink:Object(b.createElement)(R.Link,{href:"mailto:"+e.reviewer_email,onClick:function(){return n.recordReviewEvent("customer")},type:"external"})}}),o=Object(b.createElement)(b.Fragment,null,Object(b.createElement)(R.ReviewRating,{review:e}),e.verified&&Object(b.createElement)("span",{className:"woocommerce-review-activity-card__verified"},Object(b.createElement)(O.a,{icon:"checkmark",size:18}),Object(f.__)("Verified customer",'woocommerce'))),i=Object(j.get)(a,["images",0])||Object(j.get)(a,["image"]),s=v()("woocommerce-review-activity-card__image-overlay__product",{"is-placeholder":!i||!i.src}),l=Object(b.createElement)("div",{className:"woocommerce-review-activity-card__image-overlay"},Object(b.createElement)(R.Gravatar,{user:e.reviewer_email,size:24}),Object(b.createElement)("div",{className:s},Object(b.createElement)(R.ProductImage,{product:a}))),m={date:e.date_created_gmt,status:e.status},u=Object(b.createElement)(h.a,{isSecondary:!0,onClick:function(){return Object(C.recordEvent)("review_manage_click",m)},href:Object(k.f)("comment.php?action=editcomment&c="+e.id)},Object(f.__)("Manage",'woocommerce'));return Object(b.createElement)(x.a,{className:"woocommerce-review-activity-card",key:e.id,title:r,subtitle:o,date:e.date_created_gmt,icon:l,actions:u,unread:"hold"===e.status||!c||!e.date_created_gmt||new Date(e.date_created_gmt+"Z").getTime()>c},Object(b.createElement)("span",{dangerouslySetInnerHTML:Object(A.a)(e.review)}))}},{key:"renderEmptyMessage",value:function(){var e=this,t=this.props.lastApprovedReviewTime,n=Object(f.__)("You have no reviews to moderate",'woocommerce'),c="",a="",r="",o="",i="learn_more";if(t){((new Date).getTime()-t)/864e5>30?(c="https://woocommerce.com/posts/reviews-woocommerce-best-practices/",a="_blank",r=Object(f.__)("Learn more",'woocommerce'),o=Object(b.createElement)(b.Fragment,null,Object(b.createElement)("p",null,Object(f.__)("We noticed that it's been a while since your products had any reviews.",'woocommerce')),Object(b.createElement)("p",null,Object(f.__)("Take some time to learn about best practices for collecting and using your reviews.",'woocommerce')))):(c=Object(k.f)("edit-comments.php?comment_type=review"),r=Object(f.__)("View all Reviews",'woocommerce'),o=Object(b.createElement)("p",null,Object(f.__)("Awesome, you've moderated all of your product reviews. How about responding to some of those negative reviews?",'woocommerce')),i="view_reviews")}else c="https://woocommerce.com/posts/reviews-woocommerce-best-practices/",a="_blank",r=Object(f.__)("Learn more",'woocommerce'),o=Object(b.createElement)(b.Fragment,null,Object(b.createElement)("p",null,Object(f.__)("Your customers haven't started reviewing your products.",'woocommerce')),Object(b.createElement)("p",null,Object(f.__)("Take some time to learn about best practices for collecting and using your reviews.",'woocommerce')));return Object(b.createElement)(x.a,{className:"woocommerce-empty-activity-card",title:n,icon:Object(b.createElement)(O.a,{icon:"time",size:48}),actions:Object(b.createElement)(h.a,{href:c,target:a,isSecondary:!0,onClick:function(){return e.recordReviewEvent(i)}},r)},o)}},{key:"render",value:function(){var e=this,t=this.props,n=t.isError,c=t.isRequesting,a=t.reviews;if(n){var r=Object(f.__)("There was an error getting your reviews. Please try again.",'woocommerce'),o=Object(f.__)("Reload",'woocommerce');return Object(b.createElement)(b.Fragment,null,Object(b.createElement)(R.EmptyContent,{title:r,actionLabel:o,actionURL:null,actionCallback:function(){window.location.reload()}}))}var i=c||a.length?Object(f.__)("Reviews",'woocommerce'):Object(f.__)("No reviews to moderate",'woocommerce');return Object(b.createElement)(b.Fragment,null,Object(b.createElement)(T.a,{title:i}),Object(b.createElement)(R.Section,null,c?Object(b.createElement)(x.b,{className:"woocommerce-review-activity-card",hasAction:!0,hasDate:!0,lines:2}):Object(b.createElement)(b.Fragment,null,a.length?a.map((function(t){return e.renderReview(t,e.props)})):this.renderEmptyMessage())))}}]),n}(b.Component);M.propTypes={reviews:N.a.array.isRequired,isError:N.a.bool,isRequesting:N.a.bool},M.defaultProps={reviews:[],isError:!1,isRequesting:!1},t.default=Object(_.withSelect)((function(e,t){var n=t.hasUnapprovedReviews,c=e(D.REVIEWS_STORE_NAME),a=c.getReviews,r=c.getReviewsError,o=c.isResolving,i=[],s=!1,l=!1,m=null;if(n){var u={page:1,per_page:D.QUERY_DEFAULTS.pageSize,status:"hold",_embed:1};i=a(u),s=Boolean(r(u)),l=o("getReviews",[u])}else{var d={page:1,per_page:1,status:"approved",_embed:1},b=a(d);if(b.length){var f=b[0];if(f.date_created_gmt)m=new Date(f.date_created_gmt).getTime()}s=Boolean(r(d)),l=o("getReviews",[d])}return{reviews:i,isError:s,isRequesting:l,lastApprovedReviewTime:m}}))(M)}}]);