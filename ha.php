<?php
define('HA_BOOT', 1);    
include_once __DIR__ . '/head_inc.php';//session_destroy();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/int/h_api.php';
//require_once __DIR__ . '/int/phpqrcode/qrlib.php';
?>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta charset="utf-8">
	<meta id="viewport" name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#1c1b21"/>
    <meta name="cleartype" content="on">
    <meta property="og:title" content="Hamrahang">
    <link rel="preload" as="image" href="">
	<title>Hamrahang</title>
	<link rel="manifest" href="/manifest.json">
	<link rel="icon" href="/favicon.png" type="image/png" sizes="16x16">
	<link rel="icon" href="/icon.png" type="image/png" sizes="256x256">
	<link rel="apple-touch-icon" href="/apple-icon.png" type="image/png" sizes="256x256">
	<link href="/main.css" rel="stylesheet" type="text/css" />
	<!-- Telegram.js -->	
	<script src="/js/telegram-web-app.js?v=9.0"></script>
<style>
.main-video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}   
.friends-button {
    bottom: 70px;
    z-index: 20;
}

.friends-button-inner {
    align-items: center;
    display: flex;
    margin: 0 auto;
    max-width: 768px;
}
.pulse {
    animation: pulse-animation 1s ease-in-out infinite !important;
}

.friends-button-inner .is-invite {
    background: #fff;
    color: #000;
    flex: 1 1 auto;
}

.button-default {
    border-radius: 8px;
    font-size: 18px;
    font-weight: 700;
    min-height: 60px;
    padding: 0 6px;
}

.button-primary {
    background: #5550ff;
    color: #fff;
}


.button {
    border: none;
    box-shadow: none;
    cursor: pointer;
    font-family: inherit;
    outline: none;
    transition: all .25s ease;
    -webkit-transition: all .25s ease;
    -webkit-tap-highlight-color: transparent;
    align-items: center;
    display: flex;
    justify-content: center;
    position: relative;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
    width: 100%;
}

.button-copy {
    background: #fff;
    color: #000;
}

.button-primary {
    background: #5550ff;
    color: #fff;
}

.friends-button-inner .is-copy {
    flex: 0 0 60px;
    margin-left: 6px;
}
.icon.is-32 {
    height: 32px;
    width: 32px;
}


.icon {
    align-items: center;
    display: flex;
    justify-content: center;
}

.friends-button-inner .is-copy svg {
    fill: black;
    margin-left: 0;
}


.icon svg {
    height: 100%;
    width: 100%;
}


.header_search {
    max-width: 50%;
    min-height: 1px;
    padding: 0 10px;
    margin: 7 auto;
    display: flex;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 8px;
    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    -webkit-transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
    transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
}
.form-inline {
    -webkit-box-align: center;
    width: 100%;
}
.search-input {
    float: right;
    display: block;
    height: 34px;
    width: 80%;
    line-height: 1.42857143;
    color: #555;
    border: none !important;
    box-shadow: none;
    background-image: none;
    background-color: transparent;
    border: 0px solid;
    outline: none !important;
}
.search-btn {
    float: left;
    padding: 10px;
    background: #fff;
    color: #555;
    border: none;
    cursor: pointer;
    font: normal normal normal 14px / 1 FontAwesome;
}
.fa {
    display: inline-block;
    font: normal normal normal 14px / 1 FontAwesome;
    font-size: inherit;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}




.artists-list {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    column-gap: 0.5rem;
    overflow: hidden;
}

.artists-list .list {
    font-size: 1.5rem;
    display: flex;
    flex-wrap: nowrap;
    align-content: center;
    align-items: center;
    justify-content: flex-start;
    flex-direction: row;
}

.artists-list .list .thumbail {
    width: 60px;
    height: 60px;
    border-radius: 50% !important;
    background-color: transparent !important;
    color: transparent !important;
    box-shadow: 0 0.1rem 1rem rgba(0, 0, 0, 0.3);
}
.artists-list .list img {
    width: 60px;
    height: 60px;
    border-radius: 50% !important;
    background-color: transparent !important;
    color: transparent !important;

}

.artists-list .list span {
    margin: 0 5px;
    padding: 0 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#hero{
    width: 240px;
    max-width: 240px;
    padding: 10px 5px;
}

#hero .artwork img {
    background-color: transparent!important;
    color: transparent!important;
    border-radius: 10px;
    pointer-events: none!important;
    -webkit-user-drag: none!important
}

#hero .content {
    margin-top: 10px;
    min-width: 0;
    width: 50%;
    padding: 5px;
}
#hero .content {
    grid-area: headings;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    align-content: flex-start;
    flex-wrap: nowrap;
}

#hero .content .title {
    padding-top: 5px;
    overflow: hidden;
    word-break: break-word;
    line-clamp: 3;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    display: -webkit-box;
    text-overflow: ellipsis;
    white-space: normal;
    align-items: center;

    justify-self: center;
    text-align: center;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#hero .artists-list .list {
    max-width: 220px;
    font-size: 1rem;
}

.carousel-controller {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 60px;
    display: flex;
    align-items: center;
    pointer-events: none;
}

.carousel-controller.back {
    left: 0px;
    justify-content: flex-start;
}

.carousel-controller.next {
    right: 0px;
    justify-content: flex-end;
}

.carousel-button-container {
    width: 50px;
    height: 100%;

    display: flex;
    align-items: center;
    justify-content: center;
}

.carousel-button {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #000000;
    cursor: pointer;
    pointer-events: auto;
    background: #ffffff;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.8);
    transition: all .3s ease;
}

.carousel-button svg {
    display: block;

    width: 20px;
    height: 20px;
}

.carousel-button:hover {
    color: #333333;
    /*background: rgba(0, 0, 0, 0.85);*/
}

.carousel-button.is-hidden {
    visibility: hidden;
    pointer-events: none;
}



.setting-tabs {
    display: table;
    width: 100%;
    table-layout: fixed;
}

.setting-tabs .active {
    color: #000000;
    background-color: #ffffff;
}

.setting-tabs {
    display: grid;
    grid-auto-flow: column;
    align-items: stretch;
    grid-template-rows: repeat(var(--grid-rows-count, 1), minmax(0, 1fr));
    gap: var(--grid-columns-gap);
    grid-gap: var(--grid-columns-gap);
    width: 100%;
    position: relative;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    scrollbar-width: none;
    margin: 0;
    padding: 0;
}

.setting-tabs .item {
    display: inline-block;
    vertical-align: middle;
    text-align: center;
    position: relative;
    white-space: nowrap;
    padding: 7px;
    width: 100px;
    height: 100px;
    color: #ffffff;
    background-color: #000000;
    cursor: pointer;
    border-radius: 10px;
}

.setting-tabs .item.active {
    background-color: #800000;
    color: #fff;
}

.setting-tabs .item.active svg,
.setting-tabs .item:hover svg,
.setting-tabs .item:focus svg {
    transform: scale(2);
}

.setting-tabs .item .icon {
    height: 60px;
}

.setting-tabs .item .icon svg {
    width: 24px;
    height: 24px;
    transition: width 0.3s ease, height 0.3s ease;
}

.setting-content {
    padding: 0 1rem;
}
.setting-content .section-header{
    background-color: #333333;
}

.setting-content .setting-row{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
}

.setting-section{
    display: inline-block;
    vertical-align: top;
    width: 100%;
}

.setting-section-header{

    
}

.setting-column {
    display: inline-block;
    vertical-align: top;
    width: 23%;
    margin-right: 2%;
    cursor: pointer;
}

.setting-column:last-child {
    margin-right: 0;
}

.setting-title {
    font-size: 16px;
    font-weight: bold;
    color: #ffffff;
    padding: 10px 0 15px 0;
    border-bottom: 1px solid #444444;
}



.setting-item {
    position: relative;
    height: 45px;
    line-height: 45px;
    padding: 0 10px;
    margin-top: 5px;
    color: #ffffff;
    border-radius: 4px;
}

.setting-item input {
    position: absolute;
    right: 10px;
    top: 13px;
}

.setting-item:hover,
.setting-item:focus,
.setting-item.focused {
    background: #a70007;
}

.setting-toggle {
    position: absolute;
    right: 10px;
    top: 8px;
    width: 50px;
    height: 26px;
    background: #555555;
    border-radius: 13px;
}

.setting-toggle span {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    background: #ffffff;
    border-radius: 10px;
}

.setting-toggle.on {
    background: #0078D4;
}

.setting-toggle.on span {
    left: 27px;
}

.quality-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quality-item {
    padding: 20px 24px;
    background-color: #1a1a1a;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
}

.quality-item.active {
    background-color: #e50914;
}

.button-row{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
}

.user-content {
    display: flex;
    justify-content: center;
    flex-wrap: nowrap;
    align-items: center;
    align-content: center;
}
    
.user-content .page-detail {
    grid-area: headings;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    align-content: flex-start;
    flex-wrap: nowrap;
    margin-top: 10px;
    min-width: 0;
    width: 50%;
    padding: 0 16px;
}

.user-content .page-detail input {
    margin-bottom: 1.5em;
}

.user-content .account-actions {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.pair-page {
    max-width: 420px;
    margin: 2rem auto;
    padding: 1rem;
    text-align: center;
}

.pair-hint {
    opacity: 0.75;
    margin-bottom: 1.25rem;
}

.pair-code-wrap {
    position: relative;
    margin: 1.5rem 0;
    cursor: text;
}

.pair-code-hidden {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
    border: 0;
    padding: 0;
    margin: 0;
    /* یا کل ردیف را بپوشاند تا موبایل کیبورد باز شود: */
    /* inset: 0; width: 100%; height: 100%; opacity: 0; */
}

.pair-code-cells {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    direction: ltr;
}

.pair-code-cell {
    width: 2.5rem;
    height: 3rem;
    line-height: 3rem;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    border-bottom: 2px solid rgba(255, 255, 255, 0.35);
    color: inherit;
    user-select: none;
}

.pair-code-cell.filled {
    border-bottom-color: currentColor;
}

.pair-code-cell.pair-code-error {
    color: #e74c3c;
    border-bottom-color: #e74c3c;
}

.pair-error {
    color: #e74c3c;
    margin: 0.75rem 0;
    display: block;
}

.pair-auth-host {
    margin-top: 1.5rem;
    text-align: start;
}

.pair-success-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    margin-top: 1rem;
}

.search-recent {
    display: flex;
    flex-wrap: wrap;
    padding: 0 1rem;
    gap: 0.5rem;
}
.search-recent-item {
    cursor: pointer;
}

@media (max-width: 768px) {

    .carousel-controller {
        display: none;
    }
    
    .carousel-container {
        scroll-snap-type: x mandatory;
    }

    .carousel-container > * {
        scroll-snap-align: start;
    }
    .setting-tabs .item{
        height: auto;
    }
    
    .setting-tabs .item .icon {
        display: none;
    }
    
    .user-content .page-detail {
        width: 100%;
        padding: 0;
    }

}
 
</style>
<style>  

.play-pause{
    cursor: pointer;
}


.maxminplayer {
    display: flex;
    justify-content: center;
    align-content: center;
    text-align: center;
    align-items: center;
    width: 30px;
    height: 22px;
    background-color: #000;
    position: absolute;
    top: -22px;
    right: 13px;
    padding: 0px;
    border-radius: 5px 5px 0 0;
    border-top: 1px solid #a73537;
    border-left: 1px solid #a73537;
    border-right: 1px solid #a73537;
    box-shadow: 0 0 4px 4px #1c1f2433;
    -webkit-box-shadow: 0 0 2px 2px rgba(0, 0, 0, .5);
    box-shadow: 0 0 2px 2px rgba(0, 0, 0, .5);
}

img, svg {
    vertical-align: middle;
}


.material-symbols-rounded{
  cursor: pointer;
}

.top-bar{
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 18px;
}

.top-bar .material-symbols-rounded{
  font-size: 24px;
}



.music-titles{
  text-align: center;
  margin: 30px 0;
  color: #d7d7d7;
}

.music-titles .name{
  font-size: 19px;
}

.music-titles .artist{
  font-size: 17px;
  margin-top: 3px;
  opacity: 0.9;
}



 
.progress-bar span{
  position: absolute;
  width: 20px;
  height: 20px;
  background-color: #a73537;
  border-radius: inherit;
  transform: translateY(-25%);
  right: -5px;
  top: 0px;
  opacity: 0;
  transition: opacity 0.3s ease-out;
}

.progress-details:hover .progress-bar span{
  opacity: 1;
}

.time{
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 7px;

}
 
.control-btn{
  margin-top: 21px;
  margin-bottom: 7px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.control-btn div{
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.control-btn div:hover{
  color:#777777;
}



.play-pause span1 {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    width: 50px;
    height: 50px;
    padding: 0;
    margin: 0;
    background-color: #fff;
    font-size: 1.2rem;
    color: #000;
    border-radius: 50%;
    transition: all .3s ease
}

.play-pause span1:hover {
    scale: .9
} 

.maxminplayer {
    display: flex;
    justify-content: center;
    align-content: center;
    text-align: center;
    align-items: center;
    width: 30px;
    height: 22px;
    background-color: #000;
    position: absolute;
    top: -22px;
    right: 13px;
    padding: 0px;
    border-radius: 5px 5px 0 0;
    border-top: 1px solid #a73537;
    border-left: 1px solid #a73537;
    border-right: 1px solid #a73537;
    box-shadow: 0 0 4px 4px #1c1f2433;
    -webkit-box-shadow: 0 0 2px 2px rgba(0, 0, 0, .5);
    box-shadow: 0 0 2px 2px rgba(0, 0, 0, .5);
}

.wlap {    
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    position: fixed;
    border-width: 3px;
    border-style: solid;
    border-color: #3d3d3d #3d3d3d #ffffff;
    border-image: initial;
    animation: 0.8s linear 0s infinite normal none running spin;
}

.spin {
    animation: spin 2s linear infinite;
    animation-direction: reverse
}

@keyframes spin-thumb {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

.player-art-spin img {
    animation: spin-thumb 21s linear infinite;
    animation-play-state: paused;
}

.player-art-spin.is-playing img {
    animation-play-state: running;
}

.player-container-mobile {
    
    
    grid-template-rows: 100px;

    z-index: 10;
    padding: 0 1rem;
    width: 100%;
    transition: 0.3s;

}



@keyframes spin {
    0% {
        transform: rotate(0deg)
    }

    to {
        transform: rotate(359deg)
    }
}

.page-detail-header-container.video-page-header{
    display: grid;
    grid-template-areas:         "artwork"        "headings";
    grid-template-columns: 1fr;
    grid-template-rows: auto;
}


/* ---------------------------------------------------------
   13. PLAYER BOX / FOOTER / CONTROLS
   --------------------------------------------------------- */
.player-box {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 10;
    pointer-events: none; /* فقط کنترل‌ها رویداد می‌گیرند تا تاچ به stage برسد */
}
.player-box .player-head,
.player-box .player-footer,
.player-box .player-progress,
.player-box .player-panel,
.player-box #BackToHome,
.player-box .btn,
.player-box .seekbar,
.player-box .form-range,
.player-box input[type="range"] {
    pointer-events: auto;
}
.player-box .player-progress,
.player-box .seekbar {
    z-index: 12;
}
.player-box input[type="range"].form-range {
    pointer-events: auto !important;
    touch-action: none;
    cursor: pointer;
}
.player {
    display: flex;
    position: relative;
    height: 100%;
    height: -webkit-fill-available;
    min-width: -webkit-fill-available;
    width: 100%;
    transition: 0.3s;
}
#player .btn {
    padding: 0.5rem;
    color: #ffffff;
}

#player .btn.active {
    border-color: #ffffff;
}

#player .btn:hover,
#player .btn:focus {
    background: #a70000;
}

#player .btn img {
    height: 100%;
    width: 100%;
}

.head-left {
    float: left;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 2;
    pointer-events: auto;
}
#BackToHome {
    z-index: 41;
    pointer-events: auto !important;
    position: relative;
}
#player-box.controls-hidden #BackToHome {
    opacity: 1;
    pointer-events: auto !important;
}

.player-footer {
    display: grid;
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    top: auto;
    width: 100%;
    height: auto;
    z-index: 2;
    padding: 8px 12px;
    box-sizing: border-box;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.75));
    margin: 0;
    grid-template-columns: auto 1fr auto;
}



.player-control {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-self: end;
    min-width: 0;
    width: 100%;
    margin-top: 10px;
    align-items: center;
    /* display: flex; */
    flex-wrap: nowrap;

   width: 100%;
}


/* ---- app-wide thin scrollbar ---- */
html, body, .main-container, .main-content, .ln,
.bottom-sheet-wrapper, .track-line-queue-container,
.section-carousel-container,
.setting-content, .maximum-player-wrapper,
.maximum-player-scroll-area, .maximum-player-queue {
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.28) transparent;
}
html::-webkit-scrollbar,
body::-webkit-scrollbar,
.main-container::-webkit-scrollbar,
.main-content::-webkit-scrollbar,
.ln::-webkit-scrollbar,
.bottom-sheet-wrapper::-webkit-scrollbar,
.track-line-queue-container::-webkit-scrollbar,
.section-carousel-container::-webkit-scrollbar,
.carousel-container::-webkit-scrollbar,
.setting-content::-webkit-scrollbar,
.maximum-player-wrapper::-webkit-scrollbar,
.maximum-player-scroll-area::-webkit-scrollbar,
.maximum-player-queue::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
html::-webkit-scrollbar-track,
body::-webkit-scrollbar-track,
.main-container::-webkit-scrollbar-track,
.main-content::-webkit-scrollbar-track,
.ln::-webkit-scrollbar-track,
.bottom-sheet-wrapper::-webkit-scrollbar-track,
.track-line-queue-container::-webkit-scrollbar-track,
.section-carousel-container::-webkit-scrollbar-track,
.carousel-container::-webkit-scrollbar-track,
.setting-content::-webkit-scrollbar-track,
.maximum-player-wrapper::-webkit-scrollbar-track,
.maximum-player-scroll-area::-webkit-scrollbar-track,
.maximum-player-queue::-webkit-scrollbar-track {
    background: transparent;
}
html::-webkit-scrollbar-thumb,
body::-webkit-scrollbar-thumb,
.main-container::-webkit-scrollbar-thumb,
.main-content::-webkit-scrollbar-thumb,
.ln::-webkit-scrollbar-thumb,
.bottom-sheet-wrapper::-webkit-scrollbar-thumb,
.track-line-queue-container::-webkit-scrollbar-thumb,
.section-carousel-container::-webkit-scrollbar-thumb,
.carousel-container::-webkit-scrollbar-thumb,
.setting-content::-webkit-scrollbar-thumb,
.maximum-player-wrapper::-webkit-scrollbar-thumb,
.maximum-player-scroll-area::-webkit-scrollbar-thumb,
.maximum-player-queue::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.22);
    border-radius: 999px;
}
html::-webkit-scrollbar-thumb:hover,
body::-webkit-scrollbar-thumb:hover,
.main-container::-webkit-scrollbar-thumb:hover,
.main-content::-webkit-scrollbar-thumb:hover,
.ln::-webkit-scrollbar-thumb:hover,
.bottom-sheet-wrapper::-webkit-scrollbar-thumb:hover,
.track-line-queue-container::-webkit-scrollbar-thumb:hover,
.section-carousel-container::-webkit-scrollbar-thumb:hover,
.carousel-container::-webkit-scrollbar-thumb:hover,
.setting-content::-webkit-scrollbar-thumb:hover,
.maximum-player-wrapper::-webkit-scrollbar-thumb:hover,
.maximum-player-scroll-area::-webkit-scrollbar-thumb:hover,
.maximum-player-queue::-webkit-scrollbar-thumb:hover {
    background: rgba(215, 6, 71, 0.7);
}

/* ---- player in-shell panel (settings / sources / queue) ---- */
.player-panel {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: auto;
    max-height: 100%;
    z-index: 30;
    background: rgba(18, 18, 22, 0.98);
    border-radius: 16px 16px 0 0;
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 -8px 28px rgba(0,0,0,0.45);
    transform: translateY(110%);
    transition: transform 0.25s ease;
}
.player-panel.is-open {
    transform: translateY(0);
}
.player-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px 8px;
    flex-shrink: 0;
    background: rgba(18, 18, 22, 0.98);
    z-index: 2;
}
.player-panel-head .title {
    font-weight: 600;
    font-size: 15px;
}
.player-panel-head .close {
    cursor: pointer;
    padding: 6px;
    opacity: 0.85;
}
.player-panel-tabs {
    display: flex;
    gap: 8px;
    padding: 0 14px 10px;
    flex-shrink: 0;
    background: rgba(18, 18, 22, 0.98);
    z-index: 2;
}
#player-panel-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow: auto;
    padding: 4px 14px 20px;
    /* scrollbar — WebKit */
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.28) transparent;
}
#player-panel-body::-webkit-scrollbar {
    width: 6px;
}
#player-panel-body::-webkit-scrollbar-track {
    background: transparent;
}
#player-panel-body::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.22);
    border-radius: 999px;
}
#player-panel-body::-webkit-scrollbar-thumb:hover {
    background: rgba(215, 6, 71, 0.7);
}
/* scrollbar عمومی پنل‌های مشابه داخل پلیر */
.player-panel * {
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.28) transparent;
}
.player-panel *::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.player-panel *::-webkit-scrollbar-track {
    background: transparent;
}
.player-panel *::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.22);
    border-radius: 999px;
}
.player-panel *::-webkit-scrollbar-thumb:hover {
    background: rgba(215, 6, 71, 0.7);
}
.player-queue-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.player-queue-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 10px;
    background: rgba(255,255,255,0.04);
    min-height: 56px;
}
.player-queue-row.playing {
    background: rgba(215, 6, 71, 0.22);
    outline: 1px solid rgba(215, 6, 71, 0.45);
}
.player-queue-row .pq-num {
    width: 22px;
    text-align: center;
    font-size: 13px;
    opacity: 0.85;
    flex-shrink: 0;
}
.player-queue-row .pq-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
}
.player-queue-row .pq-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.player-queue-row .pq-meta {
    flex: 1;
    min-width: 0;
}
.player-queue-row .pq-meta .n1 {
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.player-queue-row .pq-meta .n1 .alt-names {
    opacity: 0.4;
    margin-left: 4px;
}
.player-queue-row .pq-meta .n2 {
    font-size: 11px;
    opacity: 0.7;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.player-queue-row .pq-date,
.player-queue-row .pq-dur {
    flex-shrink: 0;
    font-size: 11px;
    opacity: 0.75;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}
.player-queue-row .pq-date {
    width: 92px;
    text-align: center;
}
.player-queue-row .pq-dur {
    width: 44px;
    text-align: right;
}
.player-queue-row .pq-actions {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-shrink: 0;
}
.player-queue-row .pq-actions .more-btn {
    cursor: pointer;
    padding: 2px;
    opacity: 0.85;
}
.player-queue-row .pq-remove {
    cursor: pointer;
    font-size: 11px;
    opacity: 0.75;
    padding: 4px 6px;
    border-radius: 6px;
    background: rgba(255,255,255,0.06);
    flex-shrink: 0;
}
/* max-queue track-line: fixed columns for date/duration */
.track-line-queue-container .track-line .meta .date {
    min-width: 92px;
    display: inline-block;
    text-align: center;
    font-variant-numeric: tabular-nums;
}
.track-line-queue-container .track-line .meta .duration {
    min-width: 44px;
    display: inline-block;
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.track-line-queue-container .track-line .title .alt-names {
    opacity: 0.4;
    margin-left: 4px;
    font-weight: normal;
}
.track-line-queue-container .track-line .artwork {
    width: 52px;
    min-width: 52px;
}

.player-panel-tabs .tab {
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 13px;
    cursor: pointer;
    background: rgba(255,255,255,0.06);
}
.player-panel-tabs .tab.active {
    background: rgba(215, 6, 71, 0.85);
}
.player-panel-section {
    margin-bottom: 16px;
}
.player-panel-section .sec-title {
    font-size: 12px;
    opacity: 0.7;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.player-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 4px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    gap: 12px;
}
.player-toggle-row .label {
    font-size: 14px;
}
.player-toggle {
    width: 42px;
    height: 24px;
    border-radius: 999px;
    background: rgba(255,255,255,0.15);
    position: relative;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s;
}
.player-toggle span {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.2s;
}
.player-toggle.on {
    background: rgba(215, 6, 71, 0.9);
}
.player-toggle.on span {
    transform: translateX(18px);
}
.player-quality-list,
.player-source-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.player-quality-item,
.player-source-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 10px;
    cursor: pointer;
    background: rgba(255,255,255,0.04);
}
.player-quality-item.active,
.player-source-row.active {
    background: rgba(215, 6, 71, 0.22);
    outline: 1px solid rgba(215, 6, 71, 0.45);
}
.player-source-row .ps-num {
    width: 22px;
    text-align: center;
    font-size: 13px;
    opacity: 0.8;
    flex-shrink: 0;
}
.player-source-row .ps-thumb {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
}
.player-source-row .ps-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.player-source-row .ps-meta {
    flex: 1;
    min-width: 0;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.player-font-size {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.player-font-size .fs-btn {
    padding: 6px 12px;
    border-radius: 8px;
    background: rgba(255,255,255,0.06);
    cursor: pointer;
    font-size: 13px;
}
.player-font-size .fs-btn.active {
    background: rgba(215, 6, 71, 0.85);
}
#player-box.controls-hidden .player-footer,
#player-box.controls-hidden .player-progress,
#player-box.controls-hidden .player-head {
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.35s ease;
}
/* دکمه بک حتی با مخفی بودن کنترلر قابل کلیک بماند */
#player-box.controls-hidden .player-head #BackToHome,
#player-box.controls-hidden .head-left {
    opacity: 1;
    pointer-events: auto;
}
#player-box .player-footer,
#player-box .player-progress,
#player-box .player-head {
    transition: opacity 0.25s ease;
}
#media-stage .thumbail.player-art-spin {
    border-radius: 50%;
    overflow: hidden;
}
#media-stage .thumbail.player-art-spin img {
    border-radius: 50%;
}

.player-setting {
    display: flex;
    min-width: 0;
    margin: 0;
    flex-wrap: nowrap;
    align-content: center;
    justify-content: center;
    align-items: center;
    flex-wrap: nowrap;

}

#player-box .play,
#player-box .back {
    margin-left: 0;
}

#player-box .fullscreen,
#player-box .back {
    height: 3rem;
    width: 3rem;
    padding: 0.5rem;
    text-align: -webkit-center;
    align-content: center;
}

.player-progress {
    position: absolute;
    display: grid;
    grid-template-columns: auto 1fr auto;
    left: 20px;
    right: 20px;
    bottom: 70px;
    height: 30px;
    align-content: center;
    grid-auto-flow: column;
    gap: 1.3rem;
    justify-content: center;
}

.player-progress .current-time {
    font-size: 24px;
    width: 45px;
}

.player-progress .duration {
    font-size: 24px;
    width: 45px;
}

.player-progress .progress-details {
    height: 10px;
    width: auto;
    background-color: #ccc;
    border-radius: 15px;
}
#player .seekbar{
  width: 100%;
  align-content: center;
}

#player .player-progress input {
    z-index: 1;
    opacity: 1;
    width: 100%;
    height: 1rem;
    margin: 0;
    padding: 0;
    direction: ltr;
    background: transparent !important;
}

#player .player-progress input[type=range] {
    height: 20px;
    margin: 0;
    width: 100%;
    padding: 0;
    accent-color: #fff;
    background-color: transparent;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.player-progress .progress-bar-wrap {
    position: relative;
    width: 100%;
    height: 1rem;
}

.player-progress .progress-bar-track {
    direction: ltr;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 1rem;
    border-radius: 0.3125rem;
    background: #d7dcd3;
    overflow: hidden;
    opacity: 1;
    pointer-events: none;
}

.player-progress .progress-bar-fill {
    height: 100%;
    width: 0;
    background: #fc1748;
}




.focused {
    background: #d70647;
    outline: none;
}

#media-stage h1 {
    font-size: calc(2rem + 1.5vw);
    margin: 0;
}

#media-stage h1:after {
    position: absolute;
    width: 100%;
    height: 100%;
    bottom: 24px;
    left: 0;
    z-index: -1;
    transform: scaleY(-1);
    transform-origin: bottom;
    content: attr(data-content);
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 30%, rgba(94, 94, 94, .5) 80%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

#media-stage .page-detail-header-container .thumbail {
    justify-self: center;
}

.media-stage-title-wrap {
    position: relative;
    z-index: 1;
    padding-top: 8px;
    text-align: center;
}
.media-stage-header {
    justify-content: center;
}
.media-stage-artists {
    display: flex;
    padding: 8px 12px 0;
    flex-direction: column;
}
.media-stage-artists .artists-list {
    justify-content: center;
}
.media-stage-about {
    position: relative;
    z-index: 1;
}
.media-stage-about .about-container .text1 {
    opacity: 0.7;
    font-size: 12px;
}


/* ---- mini-lyric + max lyrics panel ---- */
/* سایز باکس: --ha-lyric-box-size اولویت دارد؛ وگرنه --ha-lyric-size سراسری */
.mini-lyric {
    --ha-lyric-box-size: 1.28rem;
    width: 100%;
    margin: 0 auto;
    padding: 6px 12px 10px;
    box-sizing: border-box;
    cursor: pointer;
    min-height: 10rem;
    max-height: 10rem;
    overflow: hidden;
    overscroll-behavior: contain;
    touch-action: manipulation;
    position: relative;
    z-index: 2;
    font-size: var(--ha-lyric-box-size, var(--ha-lyric-size, 16px));
    font-family: var(--ha-lyric-font, inherit);
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.mini-lyric .ha-lrc {
    max-height: 10rem;
    overflow: hidden;
    overscroll-behavior: contain;
    pointer-events: none;
    width: 100%;
}
.mini-lyric .ha-lrc.ha-lrc--focus {
    padding: 0;
    gap: 0.25rem;
    justify-content: center;
}
.mini-lyric .ha-lrc.ha-lrc--focus .ha-lrc-line.is-near {
    opacity: 0.4;
    font-size: 0.88em;
    line-height: 1.35;
}
.mini-lyric .ha-lrc.ha-lrc--focus .ha-lrc-line.active {
    font-size: 1.05em;
    font-weight: 700;
    line-height: 1.4;
}
.maximum-player-container {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
}
.maximum-player-lyrics {
    display: none;
    flex-direction: column;
    width: 100%;
    flex: 1 1 auto;
    min-height: 0;
    padding: 0 var(--main-padding-x, 1rem);
    box-sizing: border-box;
    z-index: 12;
    background: var(--bs-body-bg, #121216);
    overflow: hidden;
}
.maximum-player-lyrics.is-open {
    display: flex;
}
/* lyrics-only: کیو مخفی + ارتفاع کامل برای پنل لیریک (بدون absolute تا ارتفاع نپرد) */
.maximum-player-wrapper.ha-lyrics-only .maximum-player-queue {
    display: none !important;
}
.maximum-player-wrapper.ha-lyrics-only .maximum-player-mobile,
.maximum-player-wrapper.ha-lyrics-only .mini-lyric {
    display: none !important;
}
.maximum-player-wrapper.ha-lyrics-only .maximum-player-scroll-area,
.maximum-player-wrapper.ha-lyrics-only .maximum-player-container {
    height: 100%;
    min-height: 100%;
    width: 100%;
    max-width: none;
    align-items: stretch;
}
.maximum-player-wrapper.ha-lyrics-only .maximum-player-lyrics.is-open {
    flex: 1 1 auto;
    height: 100%;
    min-height: 100%;
}
.maximum-player-lyrics .header {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 8px 0 10px;
    background-color: var(--bs-body-bg, #121216);
    z-index: 2;
}
.maximum-player-lyrics .header .header-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    min-width: 0;
}
.maximum-player-lyrics .header .header-row-main {
    min-height: 36px;
}
.maximum-player-lyrics .header .header-row-tools {
    flex-wrap: wrap;
    gap: 8px 12px;
}
.maximum-player-lyrics .header .title {
    font-size: .95rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1 1 auto;
    max-width: none;
}
.maximum-player-lyrics .header .buttons {
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.maximum-player-lyrics .header .buttons .button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background-color: #fff;
    border-radius: 50%;
    color: #000;
    cursor: pointer;
}
.lyric-size-slider {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1 1 140px;
    min-width: 120px;
    max-width: 220px;
}
.lyric-size-slider .lz-lab {
    font-size: 11px;
    opacity: 0.7;
    flex-shrink: 0;
    width: 18px;
    text-align: center;
}
.lyric-size-slider input[type="range"] {
    flex: 1 1 auto;
    min-width: 0;
    height: 4px;
    accent-color: #d70647;
    cursor: pointer;
}
.lyric-font-keys {
    display: flex;
    flex-direction: row;
    gap: 6px;
    flex-shrink: 0;
}
.lyric-font-keys .fs-btn {
    padding: 5px 9px;
    border-radius: 8px;
    background: rgba(255,255,255,0.06);
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
    user-select: none;
}
.lyric-font-keys .fs-btn.active {
    background: rgba(215, 6, 71, 0.85);
}
/* اسکین لیریک فقط روی باکس‌های opt-in
   اولویت: --ha-lyric-box-size (per-box) → --ha-lyric-size (سراسری از ستینگ) */
.ha-lyric-skin,
[data-ha-lyric-skin] {
    font-size: var(--ha-lyric-box-size, var(--ha-lyric-size, 16px));
    font-family: var(--ha-lyric-font, inherit);
}
.ha-lyric-skin .ha-lrc-line,
[data-ha-lyric-skin] .ha-lrc-line {
    font-size: inherit;
    font-family: inherit;
}
/* لایه اسکرول‌پذیر محتوا — فونت بزرگ اسکرول را خراب نکند */
.maximum-player-lyrics .max-lyrics-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    padding: 4px 0 24px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.28) transparent;
}
.maximum-player-lyrics .max-lyrics-body .ha-lrc {
    max-height: none;
}
#media-stage-lyric.ha-lyric-skin,
#media-stage-lyric[data-ha-lyric-skin] {
    font-size: var(--ha-lyric-size, 16px);
    font-family: var(--ha-lyric-font, inherit);
}

/* ---- Lyrics engine (LRC) ---- */
.ha-lrc {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    text-align: center;
    direction: rtl;
}
.ha-lrc-line {
    opacity: 0.38;
    transition: opacity 0.25s ease, color 0.25s ease, transform 0.25s ease;
    line-height: 1.55;
    padding: 2px 8px;
    cursor: pointer;
    user-select: none;
}
.ha-lrc-line.active {
    opacity: 1;
    color: #fff;
    font-weight: 600;
    transform: scale(1.04);
}
.ha-lrc-line.past {
    opacity: 0.55;
}
.ha-lrc.static .ha-lrc-line {
    opacity: 0.9;
    cursor: default;
    transform: none;
    font-weight: normal;
}
.ha-lrc-empty {
    opacity: 0.5;
    text-align: center;
    padding: 1rem;
    font-size: 13px;
}
/* حالت خطی — فقط خط فعلی با فید نرم */
.ha-lrc.ha-lrc--line {
    display: block;
    min-height: 3.2em;
    position: relative;
    overflow: hidden;
    padding: 12px 8px;
}
.ha-lrc-line-stage {
    position: relative;
    min-height: 2.4em;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ha-lrc-fade {
    position: absolute;
    left: 0; right: 0;
    text-align: center;
    line-height: 1.5;
    padding: 4px 10px;
    font-weight: 600;
    color: #fff;
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.35s ease, transform 0.35s ease;
    pointer-events: none;
}
.ha-lrc-fade.is-in {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
    position: relative;
}
.ha-lrc-fade.is-out {
    opacity: 0;
    transform: translateY(-6px);
}
/* حالت فوکوس — قبلی / فعلی / بعدی */
.ha-lrc.ha-lrc--focus {
    gap: 0.35rem;
    padding: 8px 4px;
}
.ha-lrc.ha-lrc--focus .ha-lrc-line {
    opacity: 0;
    max-height: 0;
    padding: 0 8px;
    overflow: hidden;
    transform: scale(0.96);
    transition: opacity 0.35s ease, transform 0.35s ease, max-height 0.35s ease, padding 0.35s ease;
}
.ha-lrc.ha-lrc--focus .ha-lrc-line.is-near {
    opacity: 0.4;
    max-height: 4em;
    padding: 4px 8px;
    transform: scale(0.96);
}
.ha-lrc.ha-lrc--focus .ha-lrc-line.active {
    opacity: 1;
    max-height: 5em;
    padding: 6px 8px;
    transform: scale(1.06);
    font-weight: 700;
}
/* حالت کارت — فقط فعلی داخل باکس نرم */
.ha-lrc.ha-lrc--card {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 4.5em;
    padding: 12px;
}
.ha-lrc-card-box {
    width: 100%;
    max-width: 520px;
    padding: 14px 18px;
    border-radius: 14px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 8px 28px rgba(0,0,0,0.25);
    min-height: 2.8em;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ha-lrc-card-box .ha-lrc-fade {
    position: relative;
    left: auto; right: auto;
}
.media-stage-lyric .ha-lrc {
    max-height: 24vh;
    overflow: hidden;
    scroll-behavior: smooth;
    padding: 8px 4px 24px;
}
.media-stage-lyric .ha-lrc.ha-lrc--line,
.media-stage-lyric .ha-lrc.ha-lrc--card {
    max-height: none;
    overflow: visible;
}
.page-detail-header-container .lyric .ha-lrc {
    text-align: center;
    max-height: 40vh;
    overflow: hidden;
    padding: 0.5rem 0.75rem 1rem;
}
.page-detail-header-container .lyric .ha-lrc.ha-lrc--line,
.page-detail-header-container .lyric .ha-lrc.ha-lrc--card {
    max-height: none;
    overflow: visible;
}
.media-stage-lyric.is-hidden,
#media-stage-lyric.is-hidden {
    display: none !important;
}

.media-stage-lyric {
    position: relative;
    z-index: 1;
    padding: 8px 16px 100px;
    font-size: var(--ha-lyric-size, 16px);
}


.video-stage { max-width: 650px;position: relative; background: #000; overflow: hidden; }
.video-stage.ratio {
    position: relative;
    display: block;
}
.video-stage-thumb { position: absolute; inset: 0; z-index: 0; }
.video-stage-thumb img { width: 100%; height: 100%; object-fit: cover; }
.video-stage-slot { position: absolute; inset: 0; z-index: 1; }
.video-stage-slot video,

.video-stage-overlay {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  pointer-events: none; /* دکمه‌ها pointer-events: auto */
}
.video-stage.is-playing .video-stage-thumb { display: none; }

.video-stage .video-inline-controls {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    top: auto;
    width: 100%;
    height: auto;
    z-index: 2;
    padding: 8px 12px;
    box-sizing: border-box;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.75));
    margin: 0;
}

.video-inline-seek {
    width: 100%;
    display: block;
    margin-bottom: 6px;
}
.video-inline-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #fff;
    font-size: 12px;
}
.video-inline-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.video-inline-st,
.video-inline-fs {
    cursor: pointer;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}
 
.media-stage{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}
.media-stage .stage-bg{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    filter: blur(24px);
    transform: scale(1.1);
    opacity: 0.55;
    z-index: 0;
    pointer-events: none;
}
.media-stage #detailes-container,
.media-stage .playlist-container {
    position: relative;
    z-index: 1;
}

.vis-none {
    display: none;
    visibility: hidden;
}

/* ---- download sources list (CRBW) ---- */
.download-sources {
    padding: 0.25rem 0 0.75rem;
}
.download-source-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.download-source-row {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 52px;
    padding: 8px 14px;
    cursor: pointer;
    border-radius: 12px;
    transition: background-color .15s ease;
}
.download-source-row:hover,
.download-source-row:active {
    background-color: hsla(0, 0%, 100%, .06);
}
.download-source-row .ds-thumb {
    width: 40px;
    height: 40px;
    flex: none;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 50%;
    background: hsla(0, 0%, 100%, .07);
}
.download-source-row .ds-thumb img,
.download-source-row .ds-thumb .placeholder {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.download-source-row .ds-thumb .placeholder svg {
    width: 22px;
    height: 22px;
    opacity: 0.65;
}
.download-source-row .ds-meta {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.download-source-row .ds-owner-name {
    font-size: 14px;
    font-weight: 500;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: hsla(0, 0%, 100%, .92);
}
.bit-badge {
    flex: none;
    margin-left: auto;
    min-width: 48px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.02em;
    padding: 5px 11px;
    border-radius: 999px;
    border: 1px solid hsla(0, 0%, 100%, .12);
    background: hsla(0, 0%, 100%, .08);
    color: #cbd5e1;
}
/* audio */
.bit-badge.bit-a-320 { background: #a78bfa33; color: #c4b5fd; border-color: #a78bfa55; }
.bit-badge.bit-a-128 { background: #22c55e33; color: #86efac; border-color: #22c55e55; }
.bit-badge.bit-a-96  { background: #3b82f633; color: #93c5fd; border-color: #3b82f655; }
/* video */
.bit-badge.bit-v-1024 { background: #ef444433; color: #fca5a5; border-color: #ef444455; }
.bit-badge.bit-v-720  { background: #ec489933; color: #f9a8d4; border-color: #ec489955; }
.bit-badge.bit-v-480  { background: #f9731633; color: #fdba74; border-color: #f9731655; }
.bit-badge.bit-muted  { background: #64748b33; color: #94a3b8; border-color: #64748b44; }
</style>    

</head>
<body>
<main class="ln">
    <div class="sidebar">
        <div class="sidebar-content">
            <a class="logo-container" aria-label="Hamrahang Web Player" ><svg fill="currentColor" version="1.1" viewBox="0 0 302.45 104.79"><defs><style>.cls-1,.cls-3{fill:#fff;}.cls-1{fill-rule:evenodd;}.cls-2{fill:#1a1b1c;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><g id="radio"><polygon class="cls-1" points="30.55 0 61.09 30.55 259.22 30.55 302.45 73.75 271.91 104.29 241.36 73.75 43.22 73.75 0 30.55 30.55 0 30.55 0"/><path class="cls-2" d="M82.18,66.67H74.67V53.47H64.49v13.2H57V36.86h7.51V47.77H74.67V36.86h7.51V66.67Zm163.29,0H238V53.47H227.79v13.2h-7.51V36.86h7.51V47.77H238V36.86h7.51V66.67ZM104,59.37V53.51c-1.47.15-2.66.28-3.56.39a11.3,11.3,0,0,0-2.67.64A3.7,3.7,0,0,0,96,55.75a3.42,3.42,0,0,0-.63,2.16,3.09,3.09,0,0,0,1,2.71,5.32,5.32,0,0,0,3.1.74,5.68,5.68,0,0,0,2.32-.52A7.39,7.39,0,0,0,104,59.37Zm0,4.33c-.6.5-1.14,1-1.6,1.35a10.44,10.44,0,0,1-1.91,1.19,12.67,12.67,0,0,1-2.27.89,12.31,12.31,0,0,1-3.09.3A8.19,8.19,0,0,1,89,64.93a8.6,8.6,0,0,1-2.45-6.29,9.22,9.22,0,0,1,1.24-5,8.6,8.6,0,0,1,3.57-3.05A19,19,0,0,1,97,49q3.29-.44,7.06-.69V48.1a3.28,3.28,0,0,0-1.7-3.19,11.45,11.45,0,0,0-5.1-.89,14.09,14.09,0,0,0-3.6.56,30.51,30.51,0,0,0-4,1.43h-.74V39.25c.86-.25,2.25-.55,4.18-.89a33.51,33.51,0,0,1,5.83-.51q7.23,0,10.52,2.41c2.21,1.61,3.3,4.05,3.3,7.35V66.67H104v-3Zm48.62,3v-14c0-1.38,0-2.54-.07-3.48a7.06,7.06,0,0,0-.45-2.32,2.55,2.55,0,0,0-1.17-1.31,4.93,4.93,0,0,0-2.21-.4,4.81,4.81,0,0,0-2,.43,14.45,14.45,0,0,0-2.13,1.23V66.67h-8.73v-14c0-1.36,0-2.51-.08-3.47a7.29,7.29,0,0,0-.47-2.33,2.55,2.55,0,0,0-1.17-1.31,4.82,4.82,0,0,0-2.18-.4,4.73,4.73,0,0,0-2.1.5,14.51,14.51,0,0,0-2,1.16V66.67h-8.73V38.72h8.73v3.09a23.44,23.44,0,0,1,4-2.83,8.88,8.88,0,0,1,4.26-1,8.32,8.32,0,0,1,4.51,1.21,7.68,7.68,0,0,1,3,3.64,25.3,25.3,0,0,1,4.75-3.6,9.3,9.3,0,0,1,8-.63A6.55,6.55,0,0,1,159,40.45a8.61,8.61,0,0,1,1.76,3.19,16.48,16.48,0,0,1,.58,4.84V66.67Zm34-19.69h-.73a6.15,6.15,0,0,0-1.45-.27c-.63-.06-1.34-.08-2.1-.08a11.58,11.58,0,0,0-2.89.39,28,28,0,0,0-2.94.95v18.7h-8.73V38.72h8.73v4c.4-.36.95-.82,1.67-1.37A15.21,15.21,0,0,1,180.15,40a11.41,11.41,0,0,1,2.22-1,7.51,7.51,0,0,1,2.39-.41l.94,0,1,.08V47Zm18.71,12.39V53.51c-1.46.15-2.65.28-3.55.39a11.49,11.49,0,0,0-2.68.64,3.79,3.79,0,0,0-1.77,1.21,3.48,3.48,0,0,0-.62,2.16,3.08,3.08,0,0,0,1,2.71,5.37,5.37,0,0,0,3.11.74,5.68,5.68,0,0,0,2.32-.52,7.22,7.22,0,0,0,2.15-1.47Zm0,4.33-1.6,1.35a10,10,0,0,1-1.9,1.19,13,13,0,0,1-2.27.89,12.41,12.41,0,0,1-3.09.3,8.23,8.23,0,0,1-6.15-2.5,8.6,8.6,0,0,1-2.45-6.29,9.13,9.13,0,0,1,1.25-5,8.47,8.47,0,0,1,3.57-3.05,18.78,18.78,0,0,1,5.64-1.6q3.28-.44,7-.69V48.1a3.28,3.28,0,0,0-1.7-3.19,11.38,11.38,0,0,0-5.09-.89,14.09,14.09,0,0,0-3.6.56A30.65,30.65,0,0,0,191,46h-.74V39.25c.87-.25,2.26-.55,4.19-.89a33.43,33.43,0,0,1,5.83-.51q7.22,0,10.52,2.41c2.2,1.61,3.3,4.05,3.3,7.35V66.67h-8.73v-3Z"/><path class="cls-3" d="M109.91,104.29h-5.83l-1.52-4.85h-8.1l-1.53,4.85H87.24l8.09-24h6.5ZM101.2,95l-2.69-8.59L95.81,95Z"/><path class="cls-3" d="M132.69,104.29H127V93.72h-8.33v10.57h-5.65v-24h5.65v8.78H127V80.3h5.65Z"/><path class="cls-3" d="M158.51,104.29h-5.84l-1.52-4.85h-8.1l-1.53,4.85h-5.69l8.09-24h6.5ZM149.79,95l-2.69-8.59L144.41,95Z"/><path class="cls-3" d="M181.56,104.29h-5.44l-9.29-16.46v16.46h-5.18v-24h6.75l8,13.75V80.3h5.18Z"/><path class="cls-3" d="M206.51,102.92a27,27,0,0,1-3.9,1.25,19.13,19.13,0,0,1-4.84.62q-5.58,0-8.74-3.32t-3.16-9.2a12.67,12.67,0,0,1,3.19-9q3.18-3.42,8.89-3.43a19.57,19.57,0,0,1,4.12.43,17.6,17.6,0,0,1,4.35,1.7v5.62h-.63l-1.21-1a13.73,13.73,0,0,0-1.53-1.06,9.93,9.93,0,0,0-2-.89,8,8,0,0,0-2.42-.37,7.2,7.2,0,0,0-2.72.49,6.2,6.2,0,0,0-2.19,1.48,6.76,6.76,0,0,0-1.46,2.46,10.06,10.06,0,0,0-.54,3.44,8.71,8.71,0,0,0,1.93,6.09,7.34,7.34,0,0,0,5.69,2.11l.71,0c.26,0,.5,0,.71,0v-4.7h-4.36V91.08h10.1Z"/></g></g></g></svg></a>
            <nav class="menu">
                <div><a class="item active" href="/"><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M21,21.9h-5c-0.5,0-0.9-0.4-0.9-0.9v-4c0-1.7-1.4-3.1-3.1-3.1c-1.7,0-3.1,1.4-3.1,3.1v4c0,0.5-0.4,0.9-0.9,0.9H3    c-0.5,0-0.9-0.4-0.9-0.9v-9.6c0-0.5,0.2-1,0.6-1.3l8-7.1c0,0,0,0,0.1,0c0.7-0.5,1.8-0.5,2.5,0l8,7.1c0.4,0.3,0.7,0.8,0.7,1.4V21    C21.9,21.5,21.5,21.9,21,21.9z M3.9,21L3.9,21L3.9,21z"></path></svg><span>Home</span></a></div>
                <div><a class="item" href="/song/top"><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18.918 8.174c2.56 4.982 .501 11.656 -5.38 12.626c-7.702 1.687 -12.84 -7.716 -7.054 -13.229c.309 -.305 1.161 -1.095 1.516 -1.349c0 .528 .27 3.475 1 3.167c3 0 4 -4.222 3.587 -7.389c2.7 1.411 4.987 3.376 6.331 6.174z"></path></svg><span>Trending</span></a></div>
                <div><a class="item" href="/search"><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg><span>Search</span></a></div>
                <div><a class="item" href="/events"><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6,21c-1.7,0-3-1.3-3-3V6c0-1.7,1.3-3,3-3h12c1.7,0,3,1.3,3,3v12c0,1.7-1.3,3-3,3H6z"></path><path d="M9,13.8c0,1.7,1.3,3,3,3s3-1.3,3-3s-1.3-3-3-3S9,12.2,9,13.8"></path><path d="M15,13.8V7.3"></path></svg><span>Events</span></a></div>
                <div><a class="item" href="/library"><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path></svg><span>Library</span></a></div>
                <div><a class="item" href="/setting/account"><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22"  height="22" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><title><span>Account Settings</span></title><path d="M9.6,3.32a3.86,3.86,0,1,0,3.86,3.85A3.85,3.85,0,0,0,9.6,3.32M16.35,11a.26.26,0,0,0-.25.21l-.18,1.27a4.63,4.63,0,0,0-.82.45l-1.2-.48a.3.3,0,0,0-.3.13l-1,1.66a.24.24,0,0,0,.06.31l1,.79a3.94,3.94,0,0,0,0,1l-1,.79a.23.23,0,0,0-.06.3l1,1.67c.06.13.19.13.3.13l1.2-.49a3.85,3.85,0,0,0,.82.46l.18,1.27a.24.24,0,0,0,.25.2h1.93a.24.24,0,0,0,.23-.2l.18-1.27a5,5,0,0,0,.81-.46l1.19.49c.12,0,.25,0,.32-.13l1-1.67a.23.23,0,0,0-.06-.3l-1-.79a4,4,0,0,0,0-.49,2.67,2.67,0,0,0,0-.48l1-.79a.25.25,0,0,0,.06-.31l-1-1.66c-.06-.13-.19-.13-.31-.13L19.5,13a4.07,4.07,0,0,0-.82-.45l-.18-1.27a.23.23,0,0,0-.22-.21H16.46M9.71,13C5.45,13,2,14.7,2,16.83v1.92h9.33a6.65,6.65,0,0,1,0-5.69A13.56,13.56,0,0,0,9.71,13m7.6,1.43a1.45,1.45,0,1,1,0,2.89,1.45,1.45,0,0,1,0-2.89Z"></path></g></svg><span>Account</span></a></div>
            </nav>
            <div id=hero></div>
            <div class="links"><a href="https://hamrahang.ir/privacy-policy" target="_blank" rel="noopener noreferrer">Privacy Policy</a><a href="https://hamrahang.ir/terms-of-use" target="_blank" rel="noopener noreferrer">Terms of Use</a><a href="https://hamrahang.ir/contact" target="_blank" rel="noopener noreferrer">Contact</a><a href="https://hamrahang.ir/help" target="_blank" rel="noopener noreferrer">Help</a><a href="https://hamrahang.ir" target="_blank" rel="noopener noreferrer">Download App</a></div>
            <div class="owner"><span>@ 2025 <a href="/">Hamrahang</a></span></div>
        </div>
    </div>
    <div class="main-container">
           
        <div class="topbar-container">
            <div class="desktop-navigation">
                <div class="back" title="Back"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></div>
                <a class="logo-container-m" aria-label="Hamrahang Web Player" ><svg fill="currentColor" version="1.1" viewBox="0 0 302.45 104.79"><defs><style>.cls-1,.cls-3{fill:#fff;}.cls-1{fill-rule:evenodd;}.cls-2{fill:#1a1b1c;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><g id="radio"><polygon class="cls-1" points="30.55 0 61.09 30.55 259.22 30.55 302.45 73.75 271.91 104.29 241.36 73.75 43.22 73.75 0 30.55 30.55 0 30.55 0"/><path class="cls-2" d="M82.18,66.67H74.67V53.47H64.49v13.2H57V36.86h7.51V47.77H74.67V36.86h7.51V66.67Zm163.29,0H238V53.47H227.79v13.2h-7.51V36.86h7.51V47.77H238V36.86h7.51V66.67ZM104,59.37V53.51c-1.47.15-2.66.28-3.56.39a11.3,11.3,0,0,0-2.67.64A3.7,3.7,0,0,0,96,55.75a3.42,3.42,0,0,0-.63,2.16,3.09,3.09,0,0,0,1,2.71,5.32,5.32,0,0,0,3.1.74,5.68,5.68,0,0,0,2.32-.52A7.39,7.39,0,0,0,104,59.37Zm0,4.33c-.6.5-1.14,1-1.6,1.35a10.44,10.44,0,0,1-1.91,1.19,12.67,12.67,0,0,1-2.27.89,12.31,12.31,0,0,1-3.09.3A8.19,8.19,0,0,1,89,64.93a8.6,8.6,0,0,1-2.45-6.29,9.22,9.22,0,0,1,1.24-5,8.6,8.6,0,0,1,3.57-3.05A19,19,0,0,1,97,49q3.29-.44,7.06-.69V48.1a3.28,3.28,0,0,0-1.7-3.19,11.45,11.45,0,0,0-5.1-.89,14.09,14.09,0,0,0-3.6.56,30.51,30.51,0,0,0-4,1.43h-.74V39.25c.86-.25,2.25-.55,4.18-.89a33.51,33.51,0,0,1,5.83-.51q7.23,0,10.52,2.41c2.21,1.61,3.3,4.05,3.3,7.35V66.67H104v-3Zm48.62,3v-14c0-1.38,0-2.54-.07-3.48a7.06,7.06,0,0,0-.45-2.32,2.55,2.55,0,0,0-1.17-1.31,4.93,4.93,0,0,0-2.21-.4,4.81,4.81,0,0,0-2,.43,14.45,14.45,0,0,0-2.13,1.23V66.67h-8.73v-14c0-1.36,0-2.51-.08-3.47a7.29,7.29,0,0,0-.47-2.33,2.55,2.55,0,0,0-1.17-1.31,4.82,4.82,0,0,0-2.18-.4,4.73,4.73,0,0,0-2.1.5,14.51,14.51,0,0,0-2,1.16V66.67h-8.73V38.72h8.73v3.09a23.44,23.44,0,0,1,4-2.83,8.88,8.88,0,0,1,4.26-1,8.32,8.32,0,0,1,4.51,1.21,7.68,7.68,0,0,1,3,3.64,25.3,25.3,0,0,1,4.75-3.6,9.3,9.3,0,0,1,8-.63A6.55,6.55,0,0,1,159,40.45a8.61,8.61,0,0,1,1.76,3.19,16.48,16.48,0,0,1,.58,4.84V66.67Zm34-19.69h-.73a6.15,6.15,0,0,0-1.45-.27c-.63-.06-1.34-.08-2.1-.08a11.58,11.58,0,0,0-2.89.39,28,28,0,0,0-2.94.95v18.7h-8.73V38.72h8.73v4c.4-.36.95-.82,1.67-1.37A15.21,15.21,0,0,1,180.15,40a11.41,11.41,0,0,1,2.22-1,7.51,7.51,0,0,1,2.39-.41l.94,0,1,.08V47Zm18.71,12.39V53.51c-1.46.15-2.65.28-3.55.39a11.49,11.49,0,0,0-2.68.64,3.79,3.79,0,0,0-1.77,1.21,3.48,3.48,0,0,0-.62,2.16,3.08,3.08,0,0,0,1,2.71,5.37,5.37,0,0,0,3.11.74,5.68,5.68,0,0,0,2.32-.52,7.22,7.22,0,0,0,2.15-1.47Zm0,4.33-1.6,1.35a10,10,0,0,1-1.9,1.19,13,13,0,0,1-2.27.89,12.41,12.41,0,0,1-3.09.3,8.23,8.23,0,0,1-6.15-2.5,8.6,8.6,0,0,1-2.45-6.29,9.13,9.13,0,0,1,1.25-5,8.47,8.47,0,0,1,3.57-3.05,18.78,18.78,0,0,1,5.64-1.6q3.28-.44,7-.69V48.1a3.28,3.28,0,0,0-1.7-3.19,11.38,11.38,0,0,0-5.09-.89,14.09,14.09,0,0,0-3.6.56A30.65,30.65,0,0,0,191,46h-.74V39.25c.87-.25,2.26-.55,4.19-.89a33.43,33.43,0,0,1,5.83-.51q7.22,0,10.52,2.41c2.2,1.61,3.3,4.05,3.3,7.35V66.67h-8.73v-3Z"/><path class="cls-3" d="M109.91,104.29h-5.83l-1.52-4.85h-8.1l-1.53,4.85H87.24l8.09-24h6.5ZM101.2,95l-2.69-8.59L95.81,95Z"/><path class="cls-3" d="M132.69,104.29H127V93.72h-8.33v10.57h-5.65v-24h5.65v8.78H127V80.3h5.65Z"/><path class="cls-3" d="M158.51,104.29h-5.84l-1.52-4.85h-8.1l-1.53,4.85h-5.69l8.09-24h6.5ZM149.79,95l-2.69-8.59L144.41,95Z"/><path class="cls-3" d="M181.56,104.29h-5.44l-9.29-16.46v16.46h-5.18v-24h6.75l8,13.75V80.3h5.18Z"/><path class="cls-3" d="M206.51,102.92a27,27,0,0,1-3.9,1.25,19.13,19.13,0,0,1-4.84.62q-5.58,0-8.74-3.32t-3.16-9.2a12.67,12.67,0,0,1,3.19-9q3.18-3.42,8.89-3.43a19.57,19.57,0,0,1,4.12.43,17.6,17.6,0,0,1,4.35,1.7v5.62h-.63l-1.21-1a13.73,13.73,0,0,0-1.53-1.06,9.93,9.93,0,0,0-2-.89,8,8,0,0,0-2.42-.37,7.2,7.2,0,0,0-2.72.49,6.2,6.2,0,0,0-2.19,1.48,6.76,6.76,0,0,0-1.46,2.46,10.06,10.06,0,0,0-.54,3.44,8.71,8.71,0,0,0,1.93,6.09,7.34,7.34,0,0,0,5.69,2.11l.71,0c.26,0,.5,0,.71,0v-4.7h-4.36V91.08h10.1Z"/></g></g></g></svg></a>
            <div class="forward" title="Forward"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path><path d="M13 18l6 -6"></path><path d="M13 6l6 6"></path></svg></div></div>
            <div class="search-container"><div class="content"><div class="icon"><svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg></div><form><input class="input" placeholder="Search songs, artists, albums ..." maxlength="100" spellcheck="false" autocorrect="off" autocapitalize="off" type="text" name="search"></form><button type="button" class="clear btn btn-model-2"><svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M5 12l14 0"></path><path d="M13 18l6 -6"></path><path d="M13 6l6 6"></path></svg></button></div></div>
        </div>
        <div class="main-content" >
            <div class="gradient-color-container vis-none"><div class="ratio ratio-21x9" style="background-image: radial-gradient(ellipse at center, #e4f5fc 0%,#bfe8f9 50%,#2ab0ed 100%);;"></div></div>
        </div>
  
        <div class="minimize-player-wrapper">
        <div class="seekbar"></div>
        <div class="artwork player-art-spin">
            <span class="placeholder"><svg style="width:24px;height:24px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M7 12a5 5 0 0 1 5 -5"></path><path d="M12 17a5 5 0 0 0 5 -5"></path></svg></span>
            <img alt="" src="">
        </div>
        <div class="texts"><div class="text1"></div><div class="text2"></div></div>
        <div class="button"><svg style="width:14px;height:14px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M14.7,55.1V8.9H7v46.2H14.7z M57,55.1V8.9L22.4,32L57,55.1z"></path></svg></div>
        <div class="button"><svg style="width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M18.4,57V7l39.3,25L18.4,57z"></path></svg><i class="wlap"></i></div>
        <div class="button"><svg style="width:14px;height:14px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M49.3,55.1V8.9H57v46.2H49.3z M7,55.1V8.9L41.6,32L7,55.1z"></path></svg></div>
        <div class="button" title="Close"><svg style="width:18px;height:18px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6l-12 12"></path><path d="M6 6l12 12"></path></svg></div>

    </div> 
        <div class="maximum-player-wrapper">
            <div class="maximum-player-scroll-area">
                <div style="width:100%;overflow: hidden !important;display: flex;align-items: stretch !important;flex-direction: row !important;flex-wrap: nowrap !important;scroll-behavior: auto !important;position: relative; overflow-y: scroll !important;overflow-x: hidden !important; scrollbar-width: none !important; " >
    
                <div class="maximum-player-container">
                    <div class="maximum-player-mobile">
                        <div class="minimize-close"><div class="minimize" title="Minimize"><svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" transform="rotate(90)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg></div></div>
                        <div class="artwork ratio ratio-1x1 player-art-spin">
                            <span class="placeholder">
                                <svg style="width:150px;height:150px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M7 12a5 5 0 0 1 5 -5"></path><path d="M12 17a5 5 0 0 0 5 -5"></path></svg></span>
                        </div>
                                <div class="plays-texts-likes">
                                    <div class="plays"><span><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3,12c0,5,4,9,9,9s9-4,9-9s-4-9-9-9S3,7,3,12"></path><path d="M10.5,15l5-3l-5-3V15z"></path></svg></span><span class="plays-count"></span></div>
                            <div class="texts"><span class="text1"></span><span class="text2"><small></small></span></div>
                            <div class="likes"><span><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"></path></svg></span><span class="likes-count">6</span></div></div><div class="seekbar"><input type="range" class="form-range" min="0" max="100" step="0.1"></div><div class="times"><span></span><span></span></div>
                        <div class="buttons m-top-1">
                            <div class="button"><span><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path></svg></span></div>
                            <div class="button"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M14.7,55.1V8.9H7v46.2H14.7z M57,55.1V8.9L22.4,32L57,55.1z"></path></svg></div>
                            <div class="button play-pause"><span><svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width: 20px; height: 20px;"><path d="M18.4,57V7l39.3,25L18.4,57z"></path></svg></span><i class="wlap"></i></div>
                            <div class="button"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M49.3,55.1V8.9H57v46.2H49.3z M7,55.1V8.9L41.6,32L7,55.1z"></path></svg></div>
                            <div class="button"><span><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 4l3 3l-3 3"></path><path d="M18 20l3 -3l-3 -3"></path><path d="M3 7h15"></path><path d="M3 17h15"></path></svg></span></div>
                            </div>
                            
                        <div class="buttons m-top-1"><div class="button"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path><path d="M9 12h6"></path><path d="M12 9v6"></path></svg></div><div class="button"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7,7c0,2.2,1.8,4,4,4s4-1.8,4-4s-1.8-4-4-4S7,4.8,7,7"></path><path d="M5,21v-2c0-2.2,1.8-4,4-4h3"></path><path d="M14.8,18.5c0,1.4,1.2,2.6,2.6,2.6c1.4,0,2.6-1.2,2.6-2.6c0-1.4-1.2-2.6-2.6-2.6C16,15.9,14.8,17.1,14.8,18.5"></path><path d="M20,18.5V12"></path></svg></div><div class="button"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l16 0"></path><path d="M8 12l8 0"></path><path d="M6 18l12 0"></path></svg></div><div class="button"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path><path d="M7 11l5 5l5 -5"></path><path d="M12 4l0 12"></path></svg></div><div class="button"><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M8.7 10.7l6.6 -3.4"></path><path d="M8.7 13.3l6.6 3.4"></path></svg></div></div></div>
                        
                        <div id="mini-lyric" class="mini-lyric vis-none" title="Lyrics"></div>
                        <div class="maximum-player-queue">
                            <div class="header"><div class="title">Next Playing (0)</div><div class="buttons">
                            <div class="button" title="Minimize"><svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path></svg></div></div></div>
                            <div class="track-line-queue-container"></div>
                        </div>
                        <div id="maximum-player-lyrics" class="maximum-player-lyrics vis-none">
                            <div class="header">
                                <div class="header-row header-row-main">
                                    <div class="title" id="max-lyrics-title"></div>
                                    <div class="buttons">
                                        <div class="button" id="max-lyrics-minimize" title="Minimize"><svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path></svg></div>
                                    </div>
                                </div>
                                <div class="header-row header-row-tools">
                                    <div class="lyric-size-slider" title="Font size">
                                        <span class="lz-lab">S</span>
                                        <input type="range" id="max-lyrics-size" min="12" max="32" step="1" value="16">
                                        <span class="lz-lab">XL</span>
                                    </div>
                                    <div class="lyric-font-keys" id="max-lyrics-fonts">
                                        <div class="fs-btn" data-font="f1" title="Font 1">F1</div>
                                        <div class="fs-btn" data-font="f2" title="Font 2">F2</div>
                                        <div class="fs-btn" data-font="f3" title="Font 3">F3</div>
                                        <div class="fs-btn" data-font="f4" title="Font 4">F4</div>
                                    </div>
                                </div>
                            </div>
                            <div class="max-lyrics-body ha-lyric-skin" id="max-lyrics-body" data-ha-lyric-skin="1"></div>
                        </div>
                </div>
          
                
                </div></div></div>
        <div class="maximum-player-desktop vis-none">
                        <div class="playing-track"><div class="artwork player-art-spin">
                            <span class="placeholder"><svg style="width:24px;height:24px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M7 12a5 5 0 0 1 5 -5"></path><path d="M12 17a5 5 0 0 0 5 -5"></path></svg></span></div>
                            <div class="details">
                                <div class="text1"></div>
                                <div class="text2"></div>
                                <div class="plays-likes">
                                    <div class="plays"><span><svg style="width:18px;height:18px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3,12c0,5,4,9,9,9s9-4,9-9s-4-9-9-9S3,7,3,12"></path><path d="M10.5,15l5-3l-5-3V15z"></path></svg></span><span class="plays-count"></span></div>
                                    <div class="likes"><span><svg style="width:18px;height:18px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"></path></svg></span><span class="likes-count">6</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="controll">
                            <div class="seekbar"><input type="range" class="form-range" min="0" max="100" step="0.1"></div>
                            <div class="buttons">
                                <div><span class="current-time">00:00</span></div>
                                <div class="button"><span><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path></svg></span></div>
                                <div class="button"><svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M14.7,55.1V8.9H7v46.2H14.7z M57,55.1V8.9L22.4,32L57,55.1z"></path></svg></div>
                                <div class="button play-pause"><span><svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width: 45px; height: 45px;"><path d="M25.8,43.2L43.2,32L25.8,20.8V43.2z M32,57c-3.5,0-6.7-0.7-9.8-2s-5.7-3.1-7.9-5.3s-4-4.9-5.3-7.9c-1.3-3-2-6.3-2-9.8    s0.7-6.7,2-9.8s3.1-5.7,5.3-7.9s4.9-4,7.9-5.3s6.3-2,9.8-2s6.7,0.7,9.8,2s5.7,3.1,7.9,5.3s4,4.9,5.3,7.9s2,6.3,2,9.8s-0.7,6.7-2,9.8    c-1.3,3-3.1,5.7-5.3,7.9s-4.9,4-7.9,5.3S35.5,57,32,57z"></path></svg></span><i class="wlap"></i></div>
                                <div class="button"><svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M49.3,55.1V8.9H57v46.2H49.3z M7,55.1V8.9L41.6,32L7,55.1z"></path></svg></div>
                                <div class="button"><span><svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 4l3 3l-3 3"></path><path d="M18 20l3 -3l-3 -3"></path><path d="M3 7h15"></path><path d="M3 17h15"></path></svg></span>
                            </div>
                            <div><span class="duration">00:00</span></div></div></div>
                    <div class="details">
                        <div class="buttons">
                            <div class="button"><svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l16 0"></path><path d="M8 12l8 0"></path><path d="M6 18l12 0"></path></svg> PlayList</div>
                            <div class="button"><svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16.5 19c.246 0 .45.183.492.412L17 19.5a.5.5 0 01-.5.5h-14a.5.5 0 010-1h14zm3-5c.245 0 .45.183.492.412L20 14.5c0 .276-.228.5-.5.5h-17a.505.505 0 01-.5-.5c0-.276.228-.5.5-.5h17zm2.006-5c.242 0 .444.183.486.412L22 9.5a.5.5 0 01-.494.5H2.494a.5.5 0 010-1h19.012zM19.5 4c.245 0 .45.183.492.412L20 4.5c0 .276-.228.5-.5.5h-17a.505.505 0 01-.5-.5c0-.276.228-.5.5-.5h17z"></path></svg> Lyrics</div>
                        </div>
                        <div class="buttons">
                            <div class="button"><svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l16 0"></path><path d="M8 12l8 0"></path><path d="M6 18l12 0"></path></svg> Full SC</div>
                            <div class="button"><svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l16 0"></path><path d="M8 12l8 0"></path><path d="M6 18l12 0"></path></svg> Close</div>
                            </div>
                            </div></div>
        <footer class="main-footer">
            <div class="social-media">
            <a href="tg://HamrahangBot/Play" target="_blank" rel="noopener noreferrer" aria-label="Telegram"><svg width="22" height="22" viewBox="0 0 192 192" xmlns="http://www.w3.org/2000/svg" fill="none" style="width:22px;height:22px" stroke="currentColor" stroke-linejoin="round" stroke-width="12"><path d="M23.073 88.132s65.458-26.782 88.16-36.212c8.702-3.772 38.215-15.843 38.215-15.843s13.621-5.28 12.486 7.544c-.379 5.281-3.406 23.764-6.433 43.756-4.54 28.291-9.459 59.221-9.459 59.221s-.756 8.676-7.188 10.185c-6.433 1.509-17.027-5.281-18.919-6.79-1.513-1.132-28.377-18.106-38.214-26.404-2.649-2.263-5.676-6.79.378-12.071 13.621-12.447 29.891-27.913 39.728-37.72 4.54-4.527 9.081-15.089-9.837-2.264-26.864 18.483-53.35 35.835-53.35 35.835s-6.053 3.772-17.404.377c-11.351-3.395-24.594-7.921-24.594-7.921s-9.08-5.659 6.433-11.693Z"></path></svg></a>
            <a href="https://instagram.com/hamrahang" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg style="width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M4 8a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z"></path><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path><path d="M16.5 7.5v.01"></path></svg></a>
            </div>
            <div class="links"><a href="https://hamrahang.ir/app" target="_blank" rel="noopener noreferrer">Download App</a><a href="https://hamrahang.ir/privacy-policy" target="_blank" rel="noopener noreferrer">Privacy Policy</a><a href="https://hamrahang.ir/terms-of-use" target="_blank" rel="noopener noreferrer">Terms of Use</a><a href="https://hamrahang.ir/help" target="_blank" rel="noopener noreferrer">Help</a>
            <a href="https://hamrahang.ir/contact" target="_blank" rel="noopener noreferrer">Contact</a></div><div class="owner"><span>@ 2024 <a href="/">Hamrahang</a>, Moments Music Service.</span></div>
        </footer>
        
    </div>
    <div class="navbar-m">
        <nav class="menu">
            <a class="item" href="/"><span><svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;"><path d="M11.3,3.6l-8,7.1C3.1,10.9,3,11.2,3,11.4V21h6v-5c0-1.7,1.3-3,3-3l0,0c1.7,0,3,1.3,3,3v5h6v-9.6    c0-0.3-0.1-0.6-0.3-0.7l-8-7.1C12.3,3.3,11.7,3.3,11.3,3.6z"></path></svg></span><span class="label">Home</span></a>
            <a class="item" href="/song/top"><span><svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;"><path d="M6,21c-1.7,0-3-1.3-3-3V6c0-1.7,1.3-3,3-3h12c1.7,0,3,1.3,3,3v12c0,1.7-1.3,3-3,3H6z"></path><path d="M9,13.8c0,1.7,1.3,3,3,3s3-1.3,3-3s-1.3-3-3-3S9,12.2,9,13.8"></path><path d="M15,13.8V7.3"></path></svg></span><span class="label">Song</span></a>
            <a class="item" href="/search"><span><svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px;"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg></span><span class="label">Search</span></a>
            <a class="item" href="/library"><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path></svg><span>Library</span></a>
            <a class="item" href="/setting/account"><span><svg style="margin-right:10px;width:22px;height:22px" version="1.1" width="22"  height="22" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><title><span>Account Settings</span></title><path d="M9.6,3.32a3.86,3.86,0,1,0,3.86,3.85A3.85,3.85,0,0,0,9.6,3.32M16.35,11a.26.26,0,0,0-.25.21l-.18,1.27a4.63,4.63,0,0,0-.82.45l-1.2-.48a.3.3,0,0,0-.3.13l-1,1.66a.24.24,0,0,0,.06.31l1,.79a3.94,3.94,0,0,0,0,1l-1,.79a.23.23,0,0,0-.06.3l1,1.67c.06.13.19.13.3.13l1.2-.49a3.85,3.85,0,0,0,.82.46l.18,1.27a.24.24,0,0,0,.25.2h1.93a.24.24,0,0,0,.23-.2l.18-1.27a5,5,0,0,0,.81-.46l1.19.49c.12,0,.25,0,.32-.13l1-1.67a.23.23,0,0,0-.06-.3l-1-.79a4,4,0,0,0,0-.49,2.67,2.67,0,0,0,0-.48l1-.79a.25.25,0,0,0,.06-.31l-1-1.66c-.06-.13-.19-.13-.31-.13L19.5,13a4.07,4.07,0,0,0-.82-.45l-.18-1.27a.23.23,0,0,0-.22-.21H16.46M9.71,13C5.45,13,2,14.7,2,16.83v1.92h9.33a6.65,6.65,0,0,1,0-5.69A13.56,13.56,0,0,0,9.71,13m7.6,1.43a1.45,1.45,0,1,1,0,2.89,1.45,1.45,0,0,1,0-2.89Z"></path></g></svg></span><span class="label">Account</span></a>
        </nav>
    </div>
    <div id="pair-container vis-none">
        <div id="pair-code-input-section" style="display:none;">
            <input type="text" id="pair-code-input" placeholder="کد ۶ رقمی را وارد کنید" maxlength="6">
            <button id="pair-submit-btn">تأیید اتصال</button>
        </div>
        <div id="pair-status"></div>
    </div>
</main>
<div id="player" class="player vis-none">
    <!-- media in HTML: video reparents between page stage and fullscreen shell -->
    <video id="vply" class="main-video vis-none" playsinline></video>
    <audio id="aply" class="main-audio vis-none" src="" preload="metadata"></audio>

    <!-- media-stage + chrome built by CRPL() -->
    <div id="player-box" class="player-box vis-none"></div>
</div>
<script>
/*
(function () {
    // remove serviceWorker
    if (navigator.serviceWorker &&
        navigator.serviceWorker.getRegistrations) {

        navigator.serviceWorker.getRegistrations()
            .then(function (registrations) {

                for (var i = 0; i < registrations.length; i++) {

                    registrations[i].unregister();

                }

            });

    }

    if (window.caches) {

        caches.delete("hamrahang-v1");

    }

})();
*/
/*
(function () {
    // register serviceWorker
    if (
        navigator.serviceWorker &&
        navigator.serviceWorker.register
    ) {

        navigator.serviceWorker.register("/sw.js")
            .then(function (registration) {

                self._next.pwa = self._next.pwa || {};
                self._next.pwa.registered = true;

                console.log(
                    "Service Worker registered:",
                    registration.scope
                );

            })
            .catch(function (error) {

                console.error(
                    "Service Worker registration failed:",
                    error
                );

            });

    }

})();*/
</script>
<script>
(function () {
    
    self._next = self._next || [];
    var SN = self._next;

    SN.version = SN.version || "1.0.0";
    
    SN.config = SN.config || {};
    SN.config.home = "https://hamrahang.ir/";
    SN.config.api = "https://hamrahang.ir/";

    SN.state = SN.state || {};
    SN.state.ready = false;
    SN.state.online = navigator.onLine !== false;

    SN.user = SN.user || [];
    SN.page = SN.page || [];
    SN.player = SN.player || [];
    SN.cache = SN.cache || [];
    
    SN.dom = SN.dom || {};
    
    SN.pwa = SN.pwa || {};
    SN.pwa.supported = false;
    SN.pwa.registered = false;
    
    // Browser online/offline state
    window.addEventListener("online", function () {

        SN.state.online = true;

        console.log("Hamrahang: online");

    }, false);
    
    window.addEventListener("offline", function () {

        SN.state.online = false;

        console.log("Hamrahang: offline");

    }, false);
    

    // DOM Ready
    function ready(callback) {

        if (document.readyState === "loading") {

            document.addEventListener(
                "DOMContentLoaded",
                callback,
                false
            );

        } else {

            callback();

        }
    }
    
    ready(function () {

        SN.dom.app = document.getElementById("app");

        SN.state.ready = true;

        console.log(
            "Hamrahang application ready"
        );

        console.log(
            "Version:",
            SN.version
        );

        console.log(
            "Online:",
            SN.state.online
        );

    });

})(); 
</script>

<script>
(function () {

    self._next = self._next || [];

    var SN = self._next;

    SN.user = SN.user || [];
    SN.user.fv = SN.user.fv || [];


    /*
     * ==========================================
     * Hamrahang
     * ==========================================
     */

    var HP = "https://hamrahang.ir/";


    /*
     * ==========================================
     * API
     * ==========================================
     */

    function gu(td) {

        var xhr = new XMLHttpRequest();

        xhr.open(
            "GET",
            HP + "?_qjs=ui&" + td,
            true
        );

        xhr.onreadystatechange = function () {

            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status >= 200 && xhr.status < 300) {

                var jd;

                try {

                    jd = JSON.parse(
                        xhr.responseText
                    );

                } catch (e) {

                    console.error(
                        "Invalid JSON response"
                    );

                    return;
                }

                SN.user = jd;

                console.log(jd);

            } else {

                console.error(
                    "Response status: " + xhr.status
                );
            }
        };

        xhr.onerror = function () {

            console.error(
                "Network error"
            );

        };

        xhr.send(null);
    }


    /*
     * ==========================================
     * Telegram WebApp
     * ==========================================
     */

    var tlgrm = null;

    if (
        window.Telegram &&
        window.Telegram.WebApp
    ) {

        tlgrm = window.Telegram.WebApp;

        tlgrm.ready();
        tlgrm.expand();

        if (tlgrm.setHeaderColor) {
            tlgrm.setHeaderColor("#1c1b21");
        }

        if (tlgrm.setBackgroundColor) {
            tlgrm.setBackgroundColor("#1c1b21");
        }


        /*
         * Telegram initData
         */

        if (tlgrm.initData) {
            gu(tlgrm.initData);
        }

    }


    /*
     * ==========================================
     * PHP Session
     * ==========================================
     */

<?php
if (!empty($_SESSION['loggedin'])) {

    echo "SN.user.id = '" . json_encode((string)($_SESSION['tlg_id'] ?? '')) . "';";
    echo "SN.user.nn = '" . json_encode((string)($_SESSION['tlg_nn'] ?? '')) . "';";
    echo "SN.user.fn = '" . json_encode((string)($_SESSION['tlg_fn'] ?? '')) . "';";
    echo "SN.user.ln = '" . json_encode((string)($_SESSION['tlg_ln'] ?? '')) . "';";
    echo "SN.user.jd = '" . json_encode((string)($_SESSION['tlg_jd'] ?? '')) . "';";
    echo "SN.user.ap = '" . json_encode((string)($_SESSION['tlg_ap'] ?? '')) . "';";
    echo "SN.user.rf = '" . json_encode((string)($_SESSION['tlg_rf'] ?? '')) . "';";
}
?>


    /*
     * ==========================================
     * Debug
     * ==========================================
     */

    console.log(tlgrm);
    console.log(self);

})();
</script>

<script>

    var BTNback = document.querySelector(".back");
    var BTNforward = document.querySelector(".forward");
    
    if (BTNback) {
        BTNback.addEventListener("click", function () {
            window.history.back();
        });
    }
    
    if (BTNforward) {
        BTNforward.addEventListener("click", function () {
            window.history.forward();
        });
    }
    

        
        
!function(){"use strict";        
        
        /*document.addEventListener("DOMContentLoaded", function() { 
        console.log(2222);
        }
 console.log('Page has been loaded completely');
        document.addEventListener('readystatechange', event => {
            if (event.target.readyState === 'loading') {
                console.log('page is loading..'');
            } else if (event.target.readyState === 'interactive') {
                console.log('DOM tree has been loaded');
            } else if (event.target.readyState === 'complete') {
                console.log('Page has been loaded completely');
            }
        });
 */         

    // Helper
    
    function assignObject(target) {
    
        if (target == null) {
            throw new TypeError('Cannot convert undefined or null to object');
        }
    
        var to = Object(target);
    
        for (var i = 1; i < arguments.length; i++) {
    
            var source = arguments[i];
    
            if (source == null) {
                continue;
            }
    
            for (var key in Object(source)) {
                if (Object.prototype.hasOwnProperty.call(source, key)) {
                    to[key] = source[key];
                }
            }
        }
    
        return to;
    }
    
    var OA = assignObject;

    var SN=self._next;    
    var e,a={},l={};
    var MCLN = document.querySelector(".ln");
    var MCTR = document.querySelector(".main-container");
    var MCNT = document.querySelector(".main-content");

    const Video = document.getElementById('vply');      // یا .main-video
    const Audio = document.getElementById('aply');      // یا .main-audio
    const Player = document.getElementById('player');

    
    var HP = "https://hamrahang.ir/";
    var CDN = "https://cdn.hamrahang.ir";
    var CDNMP3 = "https://cdn.hamrahang.ir/mp3/"; 
    var HA = "/artist/";
    var HS = "/song/";
    var HV = "/video/";
    var HSU = "https://hamrahang.ir/song/";
    var HL = "/album/";
    var HPL = "/playlist/";
    var HSR = "/search/";
    var UT = "https://hamrahang.ir/thumb/";
    var HPLU = "https://hamrahang.ir/playlist/";
    var aaa = ["New Tracks"];

    var svg_dot = '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path></svg>';
    var svg_rdo = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 26px; height: 26px;"><path d="M12 12l0 .01"></path><path d="M14.828 9.172a4 4 0 0 1 0 5.656"></path><path d="M17.657 6.343a8 8 0 0 1 0 11.314"></path><path d="M9.168 14.828a4 4 0 0 1 0 -5.656"></path><path d="M6.337 17.657a8 8 0 0 1 0 -11.314"></path></svg>';
    var svg_ply = '<svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width:22px;height:22px"><path d="M18.4,57V7l39.3,25L18.4,57z"></path></svg>';
    var svg_pus = '<svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width: 20px; height: 20px;"><path d="M39.1,57V7h14.3v50H39.1z M10.6,57V7h14.3v50H10.6z"></path></svg>';
    var svg_dsk = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M7 12a5 5 0 0 1 5 -5"></path><path d="M12 17a5 5 0 0 0 5 -5"></path></svg>';
    var svg_sng = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 100px; height: 100px;"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M7 12a5 5 0 0 1 5 -5"></path><path d="M12 17a5 5 0 0 0 5 -5"></path></svg>';
    var svg_sfb = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M5 12l14 0"></path><path d="M13 18l6 -6"></path><path d="M13 6l6 6"></path></svg>';
    var svg_src = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>';
    var svg_eql = '<div class="playing-eq"><span class="playing-eq-bar playing-eq-bar1"></span><span class="playing-eq-bar playing-eq-bar2"></span><span class="playing-eq-bar playing-eq-bar3"></span></div>';
    var svg_tlg = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" style="width:22px;height:22px" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M21.9453125,2.7646484c-0.4393311-0.3643188-1.0431519-0.4580688-1.5722656-0.2441406L2.4560547,9.7539062c-0.788147,0.3244019-1.1640625,1.2263184-0.8396606,2.0144043c0.1677856,0.4075928,0.50177,0.723938,0.9177856,0.8693848l3.8652344,1.34375l2.0947266,6.9257812c0.0042114,0.0140991,0.0171509,0.0227051,0.022583,0.0362549c0.0216675,0.0552368,0.0533447,0.1060791,0.0933228,0.1499023c0.0148926,0.0176392,0.0311279,0.0341797,0.0484619,0.0494385c0.0521851,0.043335,0.112793,0.0753784,0.1779785,0.0940552c0.0099487,0.0029297,0.0166016,0.0117798,0.0267334,0.0140991l0.0058594-0.0002441l0.0029297,0.0012207c0.0333862,0.0074463,0.0674438,0.0110474,0.1015625,0.0107422c0.0446167-0.0015869,0.0888672-0.0092773,0.1313477-0.0228882c0.0082397-0.0022583,0.0165405-0.0016479,0.0245972-0.0043335c0.0725708-0.0255737,0.1381226-0.067627,0.1915283-0.1229248c0.0062256-0.0062256,0.015686-0.0073242,0.0216675-0.013916l3.0136719-3.3251953l4.3964844,3.4042969C17.0200195,21.3865967,17.3493652,21.500061,17.6884155,21.5c0.7333984-0.0001221,1.3664551-0.513855,1.5175781-1.2314453L22.46875,4.2509766C22.5817871,3.6968994,22.3805542,3.1256104,21.9453125,2.7646484z M9.5878906,15.2949219l-0.7072144,3.4367676l-1.4748535-4.8778076l7.3148804-3.809021l-4.9970703,4.9971313C9.654541,15.111145,9.6073608,15.1990967,9.5878906,15.2949219z M18.2265625,20.0673828c-0.0383301,0.184082-0.1696777,0.335022-0.3466797,0.3984375c-0.1729736,0.0665283-0.3682861,0.0368652-0.5136719-0.078125l-4.7636719-3.6884766c-0.2062378-0.159668-0.5006104-0.133728-0.6757812,0.0595703l-2.0956421,2.3121948l0.7059937-3.4274292l7.1884766-7.1894531c0.1953735-0.1950073,0.1956787-0.5114136,0.0006714-0.7067261c-0.1542358-0.1546021-0.3909912-0.1911011-0.5846558-0.0901489L6.7782593,13.053894l-3.9169312-1.3615112C2.6478271,11.6221313,2.5026245,11.4239502,2.5,11.1992188c-0.0103149-0.2263184,0.1236572-0.4343872,0.3339844-0.5185547l17.9140625-7.2324219c0.1871338-0.0794067,0.4030762-0.0461426,0.5576172,0.0859375c0.1538086,0.1241455,0.2244873,0.3244019,0.1826172,0.5175781L18.2265625,20.0673828z"></path></svg>';
    var svg_stg = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings shell-nav-icon" aria-hidden="true" data-variant="outline"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    var svg_ins = '<svg style="width:22px;height:22px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M4 8a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z"></path><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path><path d="M16.5 7.5v.01"></path></svg>';
    var svg_tl2 = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" ><path d="m1.95617055 11.392196c5.77764656-2.42328736 9.63031585-4.02086673 11.55800785-4.79273807 5.5039525-2.20384954 6.6476266-2.5866818 7.3930574-2.59932314.1639507-.00278035.5305319.0363352.7679878.22182361.2005031.15662277.2556695.36819788.2820684.51669348.026399.1484956.0592719.48677234.0331404.75109194-.2982611 3.0169019-1.5888322 10.33812718-2.2454015 13.71710898-.2778191 1.4297738-.8288514 1.7357846-1.3584441 1.7826999-1.1509274.1019576-2.0208916-.5588425-3.1356211-1.2622918-1.7443316-1.1007592-2.3854935-1.3972358-4.0786694-2.4713734-1.95675765-1.2413519-.8891962-1.8911034.2259543-3.0061212.2918402-.2918054 5.3989024-4.83750096 5.497052-5.24030969.0122753-.05037796-.1557336-.55407742-.2716182-.65323489-.1158847-.09915747-.2869204-.06524947-.4103446-.03828214-.17495.03822537-2.9615423 1.81132342-8.35977698 5.31929412-.79096496.5228681-1.50739646.7776269-2.1492945.7642766-.70764107-.0147176-2.06885864-.3851791-3.08078398-.7018404-1.24116762-.388398-1.69932554-.5713149-1.61342745-1.2309348.04474105-.3435709.36011227-.7024173.94611366-1.0765391z" fill="#fff" fill-rule="evenodd"/></svg>';
    var svg_ytb = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px" stroke-linecap="round" stroke-linejoin="round"><path d="M2 8a4 4 0 0 1 4 -4h12a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-12a4 4 0 0 1 -4 -4v-8z"></path><path d="M10 9l5 3l-5 3z"></path></svg>';
    var svg_a2p = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path><path d="M9 12h6"></path><path d="M12 9v6"></path></svg>';
    var svg_r2p = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path><path d="M9 12h6"></path></svg>';
    var svg_g2a = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M7,7c0,2.2,1.8,4,4,4s4-1.8,4-4s-1.8-4-4-4S7,4.8,7,7"></path><path d="M5,21v-2c0-2.2,1.8-4,4-4h3"></path><path d="M14.8,18.5c0,1.4,1.2,2.6,2.6,2.6c1.4,0,2.6-1.2,2.6-2.6c0-1.4-1.2-2.6-2.6-2.6C16,15.9,14.8,17.1,14.8,18.5"></path><path d="M20,18.5V12"></path></svg>';
    var svg_g2s = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M6.8,16.2c0,1.7,1.3,3,3,3s3-1.3,3-3s-1.3-3-3-3S6.8,14.6,6.8,16.2"></path><path d="M12.8,16.2V4.8l4.3,0"></path></svg>';
    var svg_don = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path><path d="M7 11l5 5l5 -5"></path><path d="M12 4l0 12"></path></svg>';
    var svg_cmt = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M8 9h8"></path><path d="M8 13h6"></path><path d="M9 18h-3a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-3l-3 3l-3 -3z"></path></svg>';
    var svg_lik = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"></path></svg>';
    var svg_lik2= '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"></path></svg>';
    var svg_shr = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M8.7 10.7l6.6 -3.4"></path><path d="M8.7 13.3l6.6 3.4"></path></svg>';
    var svg_app = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M12 21h-4a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8"></path><path d="M11 4h2"></path><path d="M16 22l5 -5"></path><path d="M21 21.5v-4.5h-4.5"></path><path d="M12 17v.01"></path></svg>';
    var svg_inf = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M3,12c0,5,4,9,9,9s9-4,9-9s-4-9-9-9S3,7,3,12"></path><path d="M12 7C12.5523 7 13 7.44772 13 8V13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13V8C11 7.44772 11.4477 7 12 7Z"></path><path d="M12 17C12.5523 17 13 16.5523 13 16C13 15.4477 12.5523 15 12 15C11.4477 15 11 15.4477 11 16C11 16.5523 11.4477 17 12 17Z" ></path></svg>';
    var svg_cpl = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M3,12c0,5,4,9,9,9s9-4,9-9s-4-9-9-9S3,7,3,12"></path><path d="M10.5,15l5-3l-5-3V15z"></path></svg>';
    var svg_sha = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px;"><path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M8.7 10.7l6.6 -3.4"></path><path d="M8.7 13.3l6.6 3.4"></path></svg>';
    var svg_tru = '<svg version="1.1" width="24" height="24" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><circle fill="#4CAF50" cx="24" cy="24" r="21"/><polygon fill="#CCFF90" points="34.6,14.6 21,28.2 15.4,22.6 12.6,25.4 21,33.8 37.4,17.4"/></svg>';
    var svg_adp = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 60px; height: 60px;"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path><path d="M9 12h6"></path><path d="M12 9v6"></path></svg>';
    var svg_lrc = '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16.5 19c.246 0 .45.183.492.412L17 19.5a.5.5 0 01-.5.5h-14a.5.5 0 010-1h14zm3-5c.245 0 .45.183.492.412L20 14.5c0 .276-.228.5-.5.5h-17a.505.505 0 01-.5-.5c0-.276.228-.5.5-.5h17zm2.006-5c.242 0 .444.183.486.412L22 9.5a.5.5 0 01-.494.5H2.494a.5.5 0 010-1h19.012zM19.5 4c.245 0 .45.183.492.412L20 4.5c0 .276-.228.5-.5.5h-17a.505.505 0 01-.5-.5c0-.276.228-.5.5-.5h17z"></path></svg>';
    var svg_plys= '<svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width: 45px; height: 45px;"><path d="M25.8,43.2L43.2,32L25.8,20.8V43.2z M32,57c-3.5,0-6.7-0.7-9.8-2s-5.7-3.1-7.9-5.3s-4-4.9-5.3-7.9c-1.3-3-2-6.3-2-9.8    s0.7-6.7,2-9.8s3.1-5.7,5.3-7.9s4.9-4,7.9-5.3s6.3-2,9.8-2s6.7,0.7,9.8,2s5.7,3.1,7.9,5.3s4,4.9,5.3,7.9s2,6.3,2,9.8s-0.7,6.7-2,9.8    c-1.3,3-3.1,5.7-5.3,7.9s-4.9,4-7.9,5.3S35.5,57,32,57z"></path></svg>';
    var svg_dons= '<svg version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" ><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path><path d="M7 11l5 5l5 -5"></path><path d="M12 4l0 12"></path></svg>';
    var svg_paus= '<svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width: 45px; height: 45px;"><path d="M24.5,42h5V22h-5V42z M34.5,42h5V22h-5V42z M32,57c-3.5,0-6.7-0.7-9.8-2s-5.7-3.1-7.9-5.3s-4-4.9-5.3-7.9    c-1.3-3-2-6.3-2-9.8s0.7-6.7,2-9.8s3.1-5.7,5.3-7.9s4.9-4,7.9-5.3s6.3-2,9.8-2s6.7,0.7,9.8,2s5.7,3.1,7.9,5.3s4,4.9,5.3,7.9    s2,6.3,2,9.8s-0.7,6.7-2,9.8c-1.3,3-3.1,5.7-5.3,7.9s-4.9,4-7.9,5.3S35.5,57,32,57z"></path></svg>';
    var svg_bw10= '<svg fill="currentColor" width="22px" height="22px" viewBox="0 0 56 56"><path d="M 27.9999 54.4024 C 41.0546 54.4024 51.9063 43.5742 51.9063 30.4961 C 51.9063 18.9649 43.4687 9.1914 32.5234 7.0351 L 32.5234 3.7070 C 32.5234 2.0430 31.3749 1.5976 30.0858 2.5117 L 22.6093 7.7383 C 21.5546 8.4883 21.5312 9.6133 22.6093 10.3867 L 30.0624 15.6367 C 31.3749 16.5742 32.5234 16.1289 32.5234 14.4414 L 32.5234 11.0898 C 41.3827 13.1055 47.8983 21.0039 47.8983 30.4961 C 47.8983 41.5586 39.0390 50.4180 27.9999 50.4180 C 16.9374 50.4180 8.0546 41.5586 8.0780 30.4961 C 8.1014 23.8398 11.3358 17.9570 16.3280 14.3945 C 17.2890 13.6680 17.5936 12.5664 16.9843 11.5820 C 16.4218 10.6211 15.1327 10.3633 14.1014 11.1602 C 8.0546 15.5430 4.0937 22.6211 4.0937 30.4961 C 4.0937 43.5742 14.9218 54.4024 27.9999 54.4024 Z M 33.6483 40.3633 C 37.5390 40.3633 40.0936 36.6836 40.0936 31.0586 C 40.0936 25.3867 37.5390 21.6602 33.6483 21.6602 C 29.7577 21.6602 27.2030 25.3867 27.2030 31.0586 C 27.2030 36.6836 29.7577 40.3633 33.6483 40.3633 Z M 21.4374 40.0820 C 22.3514 40.0820 22.9374 39.4492 22.9374 38.4649 L 22.9374 23.7227 C 22.9374 22.5742 22.3280 21.8476 21.2733 21.8476 C 20.6405 21.8476 20.1952 22.0586 19.3514 22.6211 L 16.0936 24.8008 C 15.5780 25.1758 15.3202 25.5976 15.3202 26.1602 C 15.3202 26.9336 15.9296 27.5898 16.6796 27.5898 C 17.1249 27.5898 17.3358 27.4961 17.8046 27.1680 L 19.9843 25.5742 L 19.9843 38.4649 C 19.9843 39.4258 20.5702 40.0820 21.4374 40.0820 Z M 33.6483 37.5273 C 31.5858 37.5273 30.2499 35.0664 30.2499 31.0586 C 30.2499 26.9805 31.5624 24.4961 33.6483 24.4961 C 35.7577 24.4961 37.0234 26.9570 37.0234 31.0586 C 37.0234 35.0664 35.7343 37.5273 33.6483 37.5273 Z"/></svg>';
    var svg_fw10= '<svg fill="currentColor" width="22px" height="22px" viewBox="0 0 56 56"><path d="M 27.9999 54.4024 C 41.0546 54.4024 51.9063 43.5742 51.9063 30.4961 C 51.9063 22.6211 47.9219 15.5430 41.8983 11.1602 C 40.8671 10.3633 39.5780 10.6211 38.9921 11.5820 C 38.4062 12.5664 38.7109 13.6680 39.6483 14.3945 C 44.6405 17.9570 47.8983 23.8398 47.9219 30.4961 C 47.9454 41.5586 39.0390 50.4180 27.9999 50.4180 C 16.9374 50.4180 8.1014 41.5586 8.1014 30.4961 C 8.1014 21.0039 14.6171 13.1055 23.4765 11.0898 L 23.4765 14.4649 C 23.4765 16.1289 24.6249 16.5742 25.8905 15.6602 L 33.3905 10.4102 C 34.4452 9.6836 34.4687 8.5586 33.3905 7.7851 L 25.9140 2.5351 C 24.6249 1.5976 23.4765 2.0430 23.4765 3.7305 L 23.4765 7.0351 C 12.5077 9.1680 4.0937 18.9649 4.0937 30.4961 C 4.0937 43.5742 14.9218 54.4024 27.9999 54.4024 Z M 33.6718 40.3633 C 37.5624 40.3633 40.0936 36.6836 40.0936 31.0586 C 40.0936 25.3867 37.5624 21.6602 33.6718 21.6602 C 29.7812 21.6602 27.2265 25.3867 27.2265 31.0586 C 27.2265 36.6836 29.7812 40.3633 33.6718 40.3633 Z M 21.4374 40.0820 C 22.3514 40.0820 22.9609 39.4492 22.9609 38.4649 L 22.9609 23.7227 C 22.9609 22.5742 22.3280 21.8476 21.2733 21.8476 C 20.6405 21.8476 20.2187 22.0586 19.3514 22.6211 L 16.1171 24.8008 C 15.5780 25.1758 15.3436 25.5976 15.3436 26.1602 C 15.3436 26.9336 15.9530 27.5898 16.7030 27.5898 C 17.1249 27.5898 17.3593 27.4961 17.8280 27.1680 L 20.0077 25.5742 L 20.0077 38.4649 C 20.0077 39.4258 20.5702 40.0820 21.4374 40.0820 Z M 33.6718 37.5273 C 31.5858 37.5273 30.2733 35.0664 30.2733 31.0586 C 30.2733 26.9805 31.5624 24.4961 33.6718 24.4961 C 35.7577 24.4961 37.0234 26.9570 37.0234 31.0586 C 37.0234 35.0664 35.7343 37.5273 33.6718 37.5273 Z"/></svg>';

    var svg_prv = '<svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M14.7,55.1V8.9H7v46.2H14.7z M57,55.1V8.9L22.4,32L57,55.1z"></path></svg>';
    var svg_nxt = '<svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor"><path d="M49.3,55.1V8.9H57v46.2H49.3z M7,55.1V8.9L41.6,32L7,55.1z"></path></svg>';
    var svg_rep = '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path></svg>';
    var svg_que = '<svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l16 0"></path><path d="M8 12l8 0"></path><path d="M6 18l12 0"></path></svg>';
    var svg_sub = '<svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 9h8"></path><path d="M8 13h6"></path><path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2"></path></svg>';
    var svg_lyr = '<svg style="width:16px;height:16px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18v-12l10 2v10"></path><path d="M9 15a3 3 0 1 1 -3 3"></path><path d="M19 17a3 3 0 1 1 -3 3"></path></svg>';
    var svg_bak = '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg>';
    
    var svg_rpt = '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path></svg>';
    var svg_rpt1= '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path><path d="M10.5 9.5l1.5 -1.5v8"></path></svg>';
    var svg_rpta= '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path><path d="M9.5 15l2.5 -6l2.5 6m-4 -2h3"></path></svg>';
    var svg_rptx= '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path><path d="M10 9.5l4 5m0 -5l-4 5"></path></svg>';
    var svg_shfx= '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 4l3 3l-3 3"></path><path d="M18 20l3 -3l-3 -3"></path><path d="M3 7h3a5 5 0 0 1 5 5a5 5 0 0 0 5 5h5"></path><path d="M21 7h-5a4.978 4.978 0 0 0 -3 1m-4 8a4.984 4.984 0 0 1 -3 1h-3"></path></svg>';
    var svg_shfo= '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 4l3 3l-3 3"></path><path d="M18 20l3 -3l-3 -3"></path><path d="M3 7h15"></path><path d="M3 17h15"></path></svg>';
    
    var svg_aup = '<svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width:22px;height:22px"><path d="M57,45.6H7L32,6.3L57,45.6z"></path></svg>';
    var svg_adw = '<svg version="1.1" width="22" height="22" viewBox="0 0 64 64" fill="currentColor" style="width:22px;height:22px"><path d="M57,18.4H7L32,57.7L57,18.4z"></path></svg>';
    var svg_usr = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path></svg>';
    var svg_vdo = '<svg fill="currentColor" width="800px" height="800px" viewBox="0 0 1920 1920" ><path d="M0 240v1440h1500.536v-438.89L1920 1574.062V346.051L1500.536 678.89V240H0Zm109.779 109.779h1280.979v556.348l177.995-141.29 241.468-191.66v773.646l-241.468-191.549-177.995-141.29v556.236H109.778V349.78Z" fill-rule="evenodd"/></svg>';
    var svg_ado = '<svg fill="currentColor" width="800px" height="800px" viewBox="0 0 1920 1920 "><path d="M1129.432 113v1694.148H936.638l-451.773-451.773h-315.45C76.01 1355.375 0 1279.365 0 1185.96V734.187c0-93.404 76.01-169.414 169.415-169.414h315.45L936.638 113h192.794Zm-112.943 112.943h-33.093l-418.68 418.68v630.901l418.68 418.68h33.093V225.944Zm655.488 135.114C1831.904 521.097 1920 733.77 1920 960.107c0 226.226-88.096 438.898-248.023 598.938l-79.851-79.85c138.694-138.695 214.93-323.018 214.93-519.087 0-196.183-76.236-380.506-214.93-519.2Zm-239.112 239.745c95.663 97.018 148.294 224.644 148.294 359.272s-52.631 262.254-148.294 359.272l-80.529-79.286c74.769-75.785 115.88-175.175 115.88-279.986 0-104.811-41.111-204.201-115.88-279.986Zm-981.092 76.914H169.415c-31.06 0-56.472 25.3-56.472 56.471v451.773c0 31.172 25.412 56.472 56.472 56.472h282.358V677.716Z" fill-rule="evenodd"/>';
    var svg_pls = '<svg fill="currentColor" version="1.1" id="Layer_1" width="22px" height="22px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve"><g><path d="M61.917,3.583h-53c-1.104,0-2.334,1.289-2.334,2.394v26c0,1.104,0.896,2,2,2s2-0.896,2-2V7.583h49v49H34.917		c-1.104,0-2,0.896-2,2s0.896,2,2,2h27c1.104,0,1.666-0.502,1.666-1.606v-53C63.583,4.872,63.021,3.583,61.917,3.583z"/>	<path d="M29.917,17.583h22c0.553,0,1-0.447,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.447-1,1S29.364,17.583,29.917,17.583z"/>	<path d="M17.917,17.583h4c0.553,0,1-0.447,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.447-1,1S17.364,17.583,17.917,17.583z"/>	<path d="M29.917,27.583h22c0.553,0,1-0.447,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.447-1,1S29.364,27.583,29.917,27.583z"/>	<path d="M17.917,27.583h4c0.553,0,1-0.447,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.447-1,1S17.364,27.583,17.917,27.583z"/>	<path d="M29.917,37.583h22c0.553,0,1-0.447,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.447-1,1S29.364,37.583,29.917,37.583z"/>	<path d="M17.917,37.583h4c0.553,0,1-0.447,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.447-1,1S17.364,37.583,17.917,37.583z"/>	<path d="M51.917,45.583h-17c-0.553,0-1,0.447-1,1s0.447,1,1,1h17c0.553,0,1-0.447,1-1S52.47,45.583,51.917,45.583z"/>	<path d="M30.865,50.583L9.813,38.458c-0.619-0.357-1.443-0.357-2.063,0s-1.167,1.018-1.167,1.732v24.25		c0,0.715,0.548,1.375,1.167,1.732c0.31,0.179,0.842,0.268,1.188,0.268s0.674-0.089,0.983-0.268l20.988-12.125		c0.619-0.357,0.984-1.018,0.984-1.732S31.484,50.94,30.865,50.583z M10.583,60.977V43.654l15,8.661L10.583,60.977z"/>	<path d="M20.934,53.147c0.171,0.113,0.363,0.168,0.554,0.168c0.323,0,0.641-0.156,0.833-0.445c0.307-0.46,0.183-1.08-0.277-1.387		l-6-4c-0.461-0.306-1.081-0.183-1.387,0.277c-0.307,0.46-0.183,1.08,0.277,1.387L20.934,53.147z"/></g></svg>';
    
    var svg_str = '<svg version="1.1" width="22" height="22" viewBox="0 0 60 60" fill="#ffffff" stroke-width="3" stroke="currentColor" ><path d="M55.818 21.578a1.002 1.002 0 00-.808-.681l-18.09-2.629-8.09-16.392a.998.998 0 00-1.792 0l-8.091 16.393-18.09 2.629a1.002 1.002 0 00-.555 1.705l13.091 12.76-3.091 18.018c-.064.375.09.754.397.978a.992.992 0 001.053.076l16.182-8.506 16.18 8.506a1 1 0 001.451-1.054l-3.09-18.017 13.091-12.761c.272-.267.37-.664.252-1.025z"></path></svg>';
    var svg_st2 = '<svg version="1.1" width="22" height="22" viewBox="0 0 60 60" fill="none" stroke-width="3" stroke="currentColor" ><path d="M55.818 21.578a1.002 1.002 0 00-.808-.681l-18.09-2.629-8.09-16.392a.998.998 0 00-1.792 0l-8.091 16.393-18.09 2.629a1.002 1.002 0 00-.555 1.705l13.091 12.76-3.091 18.018c-.064.375.09.754.397.978a.992.992 0 001.053.076l16.182-8.506 16.18 8.506a1 1 0 001.451-1.054l-3.09-18.017 13.091-12.761c.272-.267.37-.664.252-1.025z"></path></svg>';

    var svg_fav = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 6.2C5 5.07989 5 4.51984 5.21799 4.09202C5.40973 3.71569 5.71569 3.40973 6.09202 3.21799C6.51984 3 7.07989 3 8.2 3H15.8C16.9201 3 17.4802 3 17.908 3.21799C18.2843 3.40973 18.5903 3.71569 18.782 4.09202C19 4.51984 19 5.07989 19 6.2V21L12 16L5 21V6.2Z" /></svg>';
    var svg_fav2 = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 6.2C5 5.07989 5 4.51984 5.21799 4.09202C5.40973 3.71569 5.71569 3.40973 6.09202 3.21799C6.51984 3 7.07989 3 8.2 3H15.8C16.9201 3 17.4802 3 17.908 3.21799C18.2843 3.40973 18.5903 3.71569 18.782 4.09202C19 4.51984 19 5.07989 19 6.2V21L12 16L5 21V6.2Z" /></svg>';
 
    var svg_fav_m = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M5 6.2C5 5.07989 5 4.51984 5.21799 4.09202C5.40973 3.71569 5.71569 3.40973 6.09202 3.21799C6.51984 3 7.07989 3 8.2 3H15.8C16.9201 3 17.4802 3 17.908 3.21799C18.2843 3.40973 18.5903 3.71569 18.782 4.09202C19 4.51984 19 5.07989 19 6.2V21L12 16L5 21V6.2Z" /></svg>';
    var svg_fav2_m = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 15px; width: 20px; height: 20px;"><path d="M5 6.2C5 5.07989 5 4.51984 5.21799 4.09202C5.40973 3.71569 5.71569 3.40973 6.09202 3.21799C6.51984 3 7.07989 3 8.2 3H15.8C16.9201 3 17.4802 3 17.908 3.21799C18.2843 3.40973 18.5903 3.71569 18.782 4.09202C19 4.51984 19 5.07989 19 6.2V21L12 16L5 21V6.2Z" /></svg>';
 
 
    var svg_hrt = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z" /></svg>';
    var svg_hrt2 = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 6.00019C10.2006 3.90317 7.19377 3.2551 4.93923 5.17534C2.68468 7.09558 2.36727 10.3061 4.13778 12.5772C5.60984 14.4654 10.0648 18.4479 11.5249 19.7369C11.6882 19.8811 11.7699 19.9532 11.8652 19.9815C11.9483 20.0062 12.0393 20.0062 12.1225 19.9815C12.2178 19.9532 12.2994 19.8811 12.4628 19.7369C13.9229 18.4479 18.3778 14.4654 19.8499 12.5772C21.6204 10.3061 21.3417 7.07538 19.0484 5.17534C16.7551 3.2753 13.7994 3.90317 12 6.00019Z" /></svg>';
    
    var svg_fsc = '<svg version="1.1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"   style="width: 20px; height: 20px;"><path d="M4 9V4h5"></path>    <path d="M15 4h5v5"></path>    <path d="M20 15v5h-5"></path>    <path d="M9 20H4v-5"></path></svg>';
  
    var PlyLst = [];
    var AduLst = [];
    var VdoLst = [];
    var SrcLst = [];

    var svg_ffi = '';
    var svg_tig = '<svg version="1.1" width="48" height="48" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#8CBCD6" d="M40,41H8c-2.2,0-4-1.8-4-4V11c0-2.2,1.8-4,4-4h32c2.2,0,4,1.8,4,4v26C44,39.2,42.2,41,40,41z"/><circle fill="#B3DDF5" cx="35" cy="16" r="3"/><polygon fill="#9AC9E3" points="20,16 9,32 31,32"/><polygon fill="#B3DDF5" points="31,22 23,32 39,32"/><circle fill="#F44336" cx="38" cy="38" r="10"/><g fill="#ffffff"><rect x="36.5" y="32" transform="matrix(-.707 .707 -.707 -.707 91.74 38)" width="3" height="12"/><rect x="36.5" y="32" transform="matrix(-.707 -.707 .707 -.707 38 91.74)" width="3" height="12"/></g></svg>';
    var PlyLst = [];
    var AudLst = [];
    var LOAD_PAGE = '<div class="page-loading-container"><div class="spinner-large"></div></div>';
    var spn_lrg = OA(document.createElement("div"), {className: "spinner-large"});

    var TGAP = null;
    var TG = null;
    var TGID = '';
    var TGDV = false;
    
    const ITEM_TYPES = ['song', 'podcast', 'musicvideo', 'video', 'album', 'artist', 'playlist'];
    const PLAYABLE_TYPES = ['song', 'podcast', 'musicvideo', 'video'];
    const FOLLOWABLE_TYPES = ['artist', 'album', 'playlist'];
    const HISTORY_MAX = 50;
    const HISTORY_FLUSH_N = 5;
    const HISTORY_FLUSH_MS = 60000;
    const SEARCH_MAX = 10;
    
    if (self.Telegram && self.Telegram.WebApp) {
        TGAP = self.Telegram.WebApp;
        TG = TGAP.initDataUnsafe;
        TGID = TGAP.initData;
        TGDV = TGAP.platform !== 'unknown';
    } 

    function mergeArtistRefs(existing, incoming) {
        const map = new Map();
    
        const add = (ref) => {
            if (ref == null) return;
    
            const id = typeof ref === 'object' ? ref.id : ref;
            if (id == null) return;
    
            const prev = map.get(id);
    
            if (!prev) {
                map.set(id, ref);
                return;
            }
    
            // آبجکت کامل بر id خالی اولویت دارد
            if (typeof prev !== 'object' && typeof ref === 'object') {
                map.set(id, ref);
                return;
            }
    
            if (typeof prev === 'object' && typeof ref === 'object') {
                map.set(id, Object.assign({}, prev, ref));
                return;
            }
    
            // prev آبجکت است و ref فقط id → prev می‌ماند
        };
    
        if (Array.isArray(existing)) existing.forEach(add);
        if (Array.isArray(incoming)) incoming.forEach(add);
    
        return [...map.values()];
    }
    
    function UARDB(type, ar) {
        if (!SN || !type || typeof type !== 'string' || !Array.isArray(ar)) {
            return [];
        }
    
        if (!SN[type] || typeof SN[type] !== 'object' || Array.isArray(SN[type])) {
            SN[type] = {};
        }
    
        const ids = [];
        const seen = new Set();
    
        for (const item of ar) {
            let itemId = null;
    
            if (item && typeof item === 'object' && item.id != null) {
                itemId = item.id;
            } else if (typeof item === 'string' || typeof item === 'number') {
                itemId = item;
            } else {
                continue;
            }
    
            if (!seen.has(itemId)) {
                seen.add(itemId);
                ids.push(itemId);
            }
    
            // فقط آبجکت کامل را ذخیره/merge کن
            if (item && typeof item === 'object' && item.id != null) {
                const prev = SN[type][itemId];
    
                if (!prev) {
                    // کپی سطحی تا بعداً assign روی منبع JSON اثر نگذارد (اختیاری ولی بهتر)
                    SN[type][itemId] = { ...item };
                    if (Array.isArray(item.artists)) {
                        SN[type][itemId].artists = mergeArtistRefs([], item.artists);
                    }
                } else {
                    const incomingArtists = item.artists;
                    const { artists: _drop, ...rest } = item;
    
                    Object.assign(prev, rest);
    
                    if (Array.isArray(incomingArtists)) {
                        prev.artists = mergeArtistRefs(prev.artists, incomingArtists);
                    }
                    // اگر incoming artists نداشت → artists قبلی دست‌نخورده می‌ماند
                }
            }
        }
    
        return ids;
    }
    
    async function UART(Q) {
    
        let id = Q[1];
    
        if (!SN.artist || typeof SN.artist !== 'object' || Array.isArray(SN.artist)) {
            SN.artist = {};
        }
    
        // ---- حالت ۱: خود آبجکت کامل آمده (از داخل gjs صدا زده می‌شود) ----
        if (typeof id === 'object' && id !== null) {
            const found = id;
            id = found.id;
            found.full = true;
    
            if (Array.isArray(found.songs)) {
                found.songs = UARDB('song', found.songs);
            }
            if (Array.isArray(found.albums)) {
                found.albums = UARDB('album', found.albums);
            }
            if (Array.isArray(found.videos)) {
                found.videos = UARDB('video', found.videos);
            }
            if (Array.isArray(found.musicvideos)) {
                found.musicvideos = UARDB('musicvideo', found.musicvideos);
            }
            if (Array.isArray(found.podcasts)) {
                found.podcasts = UARDB('podcast', found.podcasts);
            }
    
            SN.artist[id] = found;
            return found;
        }
    
        // ---- حالت ۲: فقط id آمده ----
        if (SN.artist[id]?.full) {
            return SN.artist[id];
        }
    
        // اگر کامل نبود → از سرور بگیر
        try {
            await gjs(['artist', id]);
        } catch (err) {
            console.error('UART fetch error:', err);
            return null;
        }
    
        // حالا باید در SN موجود باشد
        return SN.artist[id] || null;
    }


    function playlistTimeline(pl) {
        const rows = [
            ...(pl.songs || []).map(c => ({ table: 'song', card: c, position: c.position })),
            ...(pl.videos || []).map(c => ({ table: 'video', card: c, position: c.position })),
        ];
        rows.sort((a, b) => a.position - b.position);
        return rows;
    }

    async function UPLS(Q) {
        let id = Q[1];
    
        if (!SN.playlist || typeof SN.playlist !== 'object' || Array.isArray(SN.playlist)) {
            SN.playlist = {};
        }
    
        // ---- آبجکت کامل (سرور یا لوکال از قبل ساخته‌شده) ----
        if (typeof id === 'object' && id !== null) {
            const found = { ...id };
            id = found.id;
            found.full = true;
            found.meta = found.meta && typeof found.meta === 'object' ? { ...found.meta } : {};
    
            const timeline = [];
            let ncs = 0, ncp = 0, ncm = 0, ncv = 0;
    
            if (Array.isArray(found.songs)) {
                for (const item of found.songs) {
                    if (!item || item.id == null) continue;
                    if (Array.isArray(item.artists)) {
                        item.artists = UARDB('artist', item.artists);
                    }
                    const table = (item.meta && item.meta.type === 'podcast') ? 'podcast' : 'song';
                    UARDB(table, [item]);
                    if (table === 'podcast') ncp++; else ncs++;
                    timeline.push({
                        id: item.id,
                        type: table,
                        position: item.position != null ? item.position : timeline.length
                    });
                }
                delete found.songs;
            }
    
            if (Array.isArray(found.videos)) {
                for (const item of found.videos) {
                    if (!item || item.id == null) continue;
                    if (Array.isArray(item.artists)) {
                        item.artists = UARDB('artist', item.artists);
                    }
                    const table = (item.meta && item.meta.type === 'video') ? 'video' : 'musicvideo';
                    UARDB(table, [item]);
                    if (table === 'video') ncv++; else ncm++;
                    timeline.push({
                        id: item.id,
                        type: table,
                        position: item.position != null ? item.position : timeline.length
                    });
                }
                delete found.videos;
            }
    
            // اگر از قبل items داشت (لوکال)
            if (!timeline.length && Array.isArray(found.items)) {
                found.items.forEach((row, i) => {
                    timeline.push({
                        id: row.id,
                        type: row.type || 'song',
                        position: row.position != null ? row.position : i
                    });
                });
            }
    
            timeline.sort((a, b) => a.position - b.position);
            found.items = timeline;
    
            found.meta.nci = timeline.length;
            if (ncs > 0) found.meta.ncs = ncs;
            if (ncp > 0) found.meta.ncp = ncp;
            if (ncm > 0) found.meta.ncm = ncm;
            if (ncv > 0) found.meta.ncv = ncv;
    
            SN.playlist[id] = found;
            return found;
        }
    
        // ---- id عددی ----
        const numId = Number(id);
    
        // لوکال: 1 .. 99 → هرگز API
        if (Number.isFinite(numId) && numId >= 1 && numId <= LOCAL_PLAYLIST_MAX) {
            LoadProfile();
            if (SN.playlist[numId]) return SN.playlist[numId];
            return null;
        }
    
        if (SN.playlist[id]?.full) {
            return SN.playlist[id];
        }
    
        try {
            await gjs(['playlist', id]);
        } catch (err) {
            console.error('UPLS fetch error:', err);
            return null;
        }
    
        return SN.playlist[id] || null;
    }
    
    async function ULBM(Q) {
    
        let id = Q[1];
        if (!SN.album || typeof SN.album !== 'object' || Array.isArray(SN.album)) SN.album = {};

        if (typeof id === 'object' && id !== null) {
            
            const found = id;
            id = found.id;
            found.full = true;
            
            if (Array.isArray(found.songs)) {
                found.songs = UARDB('song', found.songs);
            }

            if (Array.isArray(found.artists)) {
                found.artists = UARDB('artist', found.artists);
            }

            SN.album[id] = found;

            return found;
        }

        if (SN.album[id]?.full) return SN.album[id];
        
        try {
            await gjs(['album', id]);
        } catch (err) {
            console.error('ULBM fetch error:', err);
            return null;
        }
        
        if (SN.album[id]) return SN.album[id];

        return null;
        
    }
    
    async function USNG(Q) {
    
        let id = Q[1];
    
        // اطمینان از وجود جداول
        if (!SN.song || typeof SN.song !== 'object' || Array.isArray(SN.song)) SN.song = {};
        if (!SN.podcast || typeof SN.podcast !== 'object' || Array.isArray(SN.podcast)) SN.podcast = {};
    
        // ---- حالت ۱: خود آبجکت کامل آمده (از داخل gjs صدا زده می‌شود) ----
        if (typeof id === 'object' && id !== null) {
            const found = id;
            id = found.id;
            found.full = true;
    
            if (Array.isArray(found.artists)) {
                found.artists = UARDB('artist', found.artists);
            }
            // song2video → مینی‌کارت‌ها در SN؛ آرایه‌ها فقط id
            {
                const mvIn = Array.isArray(found.musicvideos) ? found.musicvideos.slice() : [];
                const vvIn = Array.isArray(found.videos) ? found.videos.slice() : [];
                // سازگاری: اگر فقط videos با type مخلوط آمده
                const mv = [];
                const vv = [];
                vvIn.forEach((v) => {
                    if (!v || typeof v !== 'object') {
                        if (v != null) vv.push(v);
                        return;
                    }
                    const isV = v.type === 'video' || (v.meta && v.meta.type === 'video');
                    if (isV) vv.push(v);
                    else mv.push(v);
                });
                mvIn.forEach((v) => mv.push(v));
                found.musicvideos = UARDB('musicvideo', mv);
                found.videos = UARDB('video', vv);
            }
            if (Array.isArray(found.albums)) {
                found.albums = UARDB('album', found.albums);
            }
    
            const table = (found.meta?.type === 'podcast') ? 'podcast' : 'song';
            SN[table][id] = found;
            collectOwnersFromItem(found);
    
            return found;
        }
    
        // ---- حالت ۲: فقط id آمده ----
        // اول چک کن آیا کامل موجود است
        if (SN.song[id]?.full) return SN.song[id];
        if (SN.podcast[id]?.full) return SN.podcast[id];
    
        // اگر نبود → از سرور بگیر
        try {
            await gjs(['song', id]);          // gjs خودش USNG را با آبجکت صدا می‌زند
        } catch (err) {
            console.error('USNG fetch error:', err);
            return null;
        }
    
        // حالا باید در SN موجود باشد
        if (SN.song[id]) return SN.song[id];
        if (SN.podcast[id]) return SN.podcast[id];
    
        return null;
    }
    
    function UVID(Q, callback) {
    
        var found = null;
        var id = Q[1];
        var table = null;
    
        if (!SN.video || typeof SN.video !== 'object' || Array.isArray(SN.video)) {SN.video = {};}
        if (!SN.musicvideo || typeof SN.musicvideo !== 'object' || Array.isArray(SN.musicvideo)) {SN.musicvideo = {};}
    
        // Q[1] = Video/MusicVideo Object (داده‌ی کامل از API)
        if (typeof id === 'object' && id !== null) {
    
            found = id;
            id = found.id;
            found.full = true;
    
            if (found.artists && Array.isArray(found.artists)) {
                found.artists = UARDB('artist', found.artists);
            }
            // song2video از سمت ویدیو → آرایه songs
            if (Array.isArray(found.songs)) {
                found.songs = UARDB('song', found.songs);
            } else {
                found.songs = [];
            }
    
            table = (found.meta && found.meta.type === 'musicvideo') ? 'musicvideo' : 'video';
    
            var isUpdate = !!SN[table][id];
            SN[table][id] = found;
            collectOwnersFromItem(found);
    
            console.log((isUpdate ? "Updated: " : "Added: ") + table, id);
    
            if (callback) {callback(true, found);}
            return;
        }
    
        // Q[1] = Video/MusicVideo ID → اول توی هر دو جدول بگرد
        if (SN.video[id]) {
            table = 'video';
        } else if (SN.musicvideo[id]) {
            table = 'musicvideo';
        }
    
        if (table) {
            found = SN[table][id];
    
            if (found && found.full) {
                if (callback) {callback(true, found);}
                return;
            }
        }
    
        console.log(found ? "Found Without Full Data → API:" : "Not Found → API:", id);
    
        gjs(Q, function (success, jd) {
    
            if (!success || !jd || (!jd.video && !jd.musicvideo)) {
                if (callback) {callback(false, null);}
                return;
            }
    
            var resolvedTable = null;
            if (SN.video[id]) {
                resolvedTable = 'video';
            } else if (SN.musicvideo[id]) {
                resolvedTable = 'musicvideo';
            }
    
            if (!resolvedTable) {
                if (callback) {callback(false, null);}
                return;
            }
    
            found = SN[resolvedTable][id];
    
            if (!found) {
                if (callback) {callback(false, null);}
                return;
            }
    
            if (callback) {callback(true, found);}
        });
    }
    
    function UHOM(Q) {
        const ar = Q[1];
        const p  = Q[0][0];
        const q  = Q[0][1];
    
        if (!ar || !Array.isArray(ar)) return;
    
        // ---- song list (latest / top / ...) ----
        if (p === 'song') {
            if (!SN.song || typeof SN.song !== 'object') SN.song = {};
            if (!SN.podcast || typeof SN.podcast !== 'object') SN.podcast = {};
    
            SN[q + '_song'] = [];
    
            for (let i = 0; i < ar.length; i++) {
                const item = ar[i];
                if (!item || item.id == null) continue;
    
                if (Array.isArray(item.artists)) {
                    item.artists = UARDB('artist', item.artists);
                }
    
                SN[q + '_song'].push(item.id);
    
                const table = (item.meta && item.meta.type === 'podcast') ? 'podcast' : 'song';
                UARDB(table, [item]);
            }
        }
    
        // ---- video list (latest / top / ...) ----
        if (p === 'video') {
            if (!SN.video || typeof SN.video !== 'object') SN.video = {};
            if (!SN.musicvideo || typeof SN.musicvideo !== 'object') SN.musicvideo = {};
    
            SN[q + '_video'] = [];
    
            for (let i = 0; i < ar.length; i++) {
                const item = ar[i];
                if (!item || item.id == null) continue;
    
                if (Array.isArray(item.artists)) {
                    item.artists = UARDB('artist', item.artists);
                }
    
                SN[q + '_video'].push(item.id);
    
                const table = (item.meta && item.meta.type === 'video') ? 'video' : 'musicvideo';
                UARDB(table, [item]);
            }
        }
    
        // ---- artist list ----
        if (p === 'artist') {
            if (!SN.artist || typeof SN.artist !== 'object') SN.artist = {};
    
            SN[q + '_artist'] = [];
    
            for (let i = 0; i < ar.length; i++) {
                const item = ar[i];
                if (!item || item.id == null) continue;
    
                SN[q + '_artist'].push(item.id);
                UARDB('artist', [item]);
            }
        }
    
        // ---- album list (اگر بعداً album/latest داشتی) ----
        if (p === 'album') {
            if (!SN.album || typeof SN.album !== 'object') SN.album = {};
    
            SN[q + '_album'] = [];
    
            for (let i = 0; i < ar.length; i++) {
                const item = ar[i];
                if (!item || item.id == null) continue;
    
                if (Array.isArray(item.artists)) {
                    item.artists = UARDB('artist', item.artists);
                }
    
                SN[q + '_album'].push(item.id);
                UARDB('album', [item]);
            }
        }
        
        // ---- playlist list ----
        if (p === 'playlist') {
            if (!SN.playlist || typeof SN.playlist !== 'object') SN.playlist = {};
    
            SN[q + '_playlist'] = [];
    
            for (let i = 0; i < ar.length; i++) {
                const item = ar[i];
                if (!item || item.id == null) continue;
    
                if (Array.isArray(item.artists)) {
                    item.artists = UARDB('artist', item.artists);
                }
    
                SN[q + '_playlist'].push(item.id);
                UARDB('playlist', [item]);
            }
        }
    }

    function USRC(Q) {
        const q = Q[1];
    
        if (!SN.search || typeof SN.search !== 'object' || Array.isArray(SN.search)) {
            SN.search = {};
        }
    
        if (typeof q !== 'object' || q === null) {
            console.log('USRC: invalid search data:', q);
            return null;
        }
    
        const out = {
            q: q.q || '',
            artists: [],
            albums: [],
            songs: [],
            podcasts: [],
            musicvideos: [],
            videos: [],
            playlists: []
        };
    
        // artists
        if (Array.isArray(q.artists)) {
            out.artists = UARDB('artist', q.artists);
        }
    
        // albums
        if (Array.isArray(q.albums)) {
            out.albums = UARDB('album', q.albums);
        }
    
        // playlists
        if (Array.isArray(q.playlists)) {
            out.playlists = UARDB('playlist', q.playlists);
        }
    
        // songs → songs + podcasts
        if (Array.isArray(q.songs)) {
            for (const item of q.songs) {
                if (!item || item.id == null) continue;
    
                if (Array.isArray(item.artists)) {
                    item.artists = UARDB('artist', item.artists);
                }
    
                if (item.meta && item.meta.type === 'podcast') {
                    UARDB('podcast', [item]);
                    out.podcasts.push(item.id);
                } else {
                    UARDB('song', [item]);
                    out.songs.push(item.id);
                }
            }
        }
    
        // videos (از جدول video) → musicvideos + videos
        if (Array.isArray(q.videos)) {
            for (const item of q.videos) {
                if (!item || item.id == null) continue;
    
                if (Array.isArray(item.artists)) {
                    item.artists = UARDB('artist', item.artists);
                }
    
                if (item.meta && item.meta.type === 'video') {
                    UARDB('video', [item]);
                    out.videos.push(item.id);
                } else {
                    // پیش‌فرض: musicvideo
                    UARDB('musicvideo', [item]);
                    out.musicvideos.push(item.id);
                }
            }
        }
    
        SN.search = out;
        return SN.search;
    }

    function mergeResolvedItems(type, items) {
        if (!Array.isArray(items)) return;
    
        for (const item of items) {
            if (!item || item.id == null) continue;
    
            if (Array.isArray(item.artists)) {
                item.artists = UARDB('artist', item.artists);
            }
    
            if (type === 'song') {
                const table = (item.meta && item.meta.type === 'podcast') ? 'podcast' : 'song';
                UARDB(table, [item]);
            } else if (type === 'video') {
                const table = (item.meta && item.meta.type === 'video') ? 'video' : 'musicvideo';
                UARDB(table, [item]);
            } else if (type === 'album') {
                UARDB('album', [item]);
            } else if (type === 'artist') {
                UARDB('artist', [item]);
            } else {
                UARDB(type, [item]);
            }
        }
    }
    
    const gjs = async (U, options = null) => {
    
        if (!U || !Array.isArray(U) || U.length < 2) {
            throw new Error('Invalid request params');
        }
    
        const type = U[0];
        const action = U[1];
        let url = `${HP}${type}/${action}?_qjs=ep`;
    
        // query برای pagination بعدی
        if (options && typeof options === 'object' && !Array.isArray(options)) {
            const qs = new URLSearchParams();
            if (options.limit != null) qs.set('limit', String(options.limit));
            if (options.cursor) qs.set('cursor', String(options.cursor));
            const qstr = qs.toString();
            if (qstr) url += `&${qstr}`;
        }
    
        let response;
    
        if (action === 'resolve') {
            const ids = Array.isArray(options)
                ? options
                : (options && options.ids) ? options.ids : [];
    
            response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json; charset=UTF-8' },
                body: JSON.stringify({ ids })
            });
        } else {
            response = await fetch(url);
        }
    
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }
    
        const jd = await response.json();
    
        // resolve → فقط SN، بدون UHOM
        if (action === 'resolve' && Array.isArray(jd)) {
            mergeResolvedItems(type, jd);
            return jd;
        }
    
        // جزئیات تکی
        if (jd && !Array.isArray(jd) && typeof jd === 'object') {
            if (jd.video) {
                UVID(['video', jd.video]);
            } else if (jd.musicvideo) {
                UVID(['video', jd.musicvideo]);
            } else if (jd.song) {
                USNG(['song', jd.song]);
            } else if (jd.podcast) {
                USNG(['song', jd.podcast]);
            } else if (jd.artist) {
                UART(['artist', jd.artist]);
            } else if (jd.album) {
                ULBM(['album', jd.album]);
            } else if (jd.playlist) {
                UPLS(['playlist', jd.playlist]);
            } else if (jd.search) {
                USRC(['search', jd.search]);
            } else {
                UHOM([U, jd]);
            }
            return jd;
        }
    
        // لیست latest / top
        if (Array.isArray(jd)) {
            UHOM([U, jd]);
        }
    
        return jd;
    };
    
    async function EnsureItems(type, ids) {
        if (!Array.isArray(ids) || !ids.length) return [];
    
        // کلید اصلی در SN
        const snKey = (type === 'podcast') ? 'podcast'
                    : (type === 'musicvideo') ? 'musicvideo'
                    : (type === 'video') ? 'video'
                    : type;
    
        if (!SN[snKey] || typeof SN[snKey] !== 'object') {
            SN[snKey] = {};
        }
        // برای song/podcast و video/musicvideo جدول خواهر را هم آماده کن
        if (type === 'song' || type === 'podcast') {
            if (!SN.song || typeof SN.song !== 'object') SN.song = {};
            if (!SN.podcast || typeof SN.podcast !== 'object') SN.podcast = {};
        }
        if (type === 'video' || type === 'musicvideo') {
            if (!SN.video || typeof SN.video !== 'object') SN.video = {};
            if (!SN.musicvideo || typeof SN.musicvideo !== 'object') SN.musicvideo = {};
        }
    
        const isPresent = (id) => {
            if (type === 'song' || type === 'podcast') {
                return !!(SN.song?.[id] || SN.podcast?.[id]);
            }
            if (type === 'video' || type === 'musicvideo') {
                return !!(SN.video?.[id] || SN.musicvideo?.[id]);
            }
            return !!(SN[snKey] && SN[snKey][id]);
        };
    
        const missing = ids.filter(id => !isPresent(id));
        if (!missing.length) {
            return ids.filter(isPresent);
        }
    
        try {
            if (type === 'song' || type === 'podcast') {
                await gjs(['song', 'resolve'], { ids: missing });
            } else if (type === 'album') {
                await gjs(['album', 'resolve'], { ids: missing });
            } else if (type === 'artist') {
                await gjs(['artist', 'resolve'], { ids: missing });
            } else if (type === 'video' || type === 'musicvideo') {
                await gjs(['video', 'resolve'], { ids: missing });
            } else {
                console.warn('EnsureItems: resolve not ready for', type);
                return ids.filter(isPresent);
            }
        } catch (err) {
            console.warn('EnsureItems failed:', type, err.message);
        }
    
        return ids.filter(isPresent);
    }
    
    async function gja(q) {
        const url = `${HP}?_qjs=uac`;
    
        const headers = {
            'Content-Type': 'application/json; charset=UTF-8'
        };
    
        if (SN.accessToken) {
            headers['Authorization'] = `Bearer ${SN.accessToken}`;
        }
    
        const response = await fetch(url, {
            method: 'POST',
            headers,
            body: JSON.stringify({ q })
        });
    
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }
    
        const j = await response.json();
    
        if (j && j.error) {
            throw new Error(j.error);
        }
    
        return j;
    }
    
    function gjl(q, callback) {

        var xhr = new XMLHttpRequest();
        var url = HP + '?_qjs=ual';
    
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    
        if (SN.accessToken) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + SN.accessToken);
        }
    
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) { return; }
        
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var j = JSON.parse(xhr.responseText);
                    if (j && j.error) {
                        if (callback) { callback(false, j); }
                        return;
                    }
                    if (callback) { callback(true, j); }
                } catch (error) {
                    console.error(error.message || error);
                    if (callback) { callback(false, null); }
                }
            } else {
                console.error('Response status: ' + xhr.status);
                if (callback) { callback(false, null); }
            }
        };
    
        xhr.onerror = function () {
            console.error('Network error');
            if (callback) { callback(false, null); }
        };
    
        xhr.send(JSON.stringify(q));
    }
    
    function LoadUserFromAPI(userId, callback) {

        gjl({ a: 'get_user', id_user: userId }, function (success, j) {
    
            if (!success || !j || !j.id_user) {
                SN.user = { loggedIn: false, id: null, name: null, fv: { sng: [], art: [], alb: [] }, playlists: [] };
                if (callback) { callback(false); }
                return;
            }
    
            SN.user = j;
            SN.user.id = j.id_user;
            SN.user.loggedIn = true;
    
            if (callback) { callback(true); }
        });
    }
    
    function GetOrCreateDeviceId() {
    
        var did = null;
        try {
            did = localStorage.getItem('did');
        } catch (e) {}
    
        if (!did) {
            did = GenerateRandomToken(32);
            try {
                localStorage.setItem('did', did);
            } catch (e) {}
        }
    
        return did;
    }
    
    function CheckLoginStatus(onUpdate) {
        const did = GetOrCreateDeviceId();
        let refreshToken = null;
        let cachedUser = null;
    
        try {
            refreshToken = localStorage.getItem('ha_rftk');
            const raw = localStorage.getItem('ha_user');
            if (raw) {
                cachedUser = JSON.parse(raw);
            }
        } catch (e) {}
    
        // نمایش فوری از کش (بدون انتظار شبکه)
        if (cachedUser && refreshToken) {
            SN.user = {
                id: cachedUser.id || null,
                name: cachedUser.name || '',
                loggedIn: true
            };
        } else {
            SN.user = { loggedIn: false };
        }
    
        // اگر توکن نیست، همان وضعیت را اعلام کن و تمام
        if (!refreshToken) {
            if (onUpdate) onUpdate();
            return;
        }
    
        // رفرش سشن از سرور
        gjl(
            {
                a: 'refresh_session',
                refresh_token: refreshToken,
                did: did
            },
            function (success, j) {
    
                // ناموفق: شبکه، خطا، یا پاسخ بدون access_token
                if (!success || !j || j.error || !j.access_token) {
                    SN.user = { loggedIn: false };
                    SN.accessToken = null;
    
                    try {
                        localStorage.removeItem('ha_rftk');
                        localStorage.removeItem('ha_user');
                    } catch (e) {}
    
                    if (onUpdate) onUpdate();
                    return;
                }
    
                // موفق
                SN.accessToken = j.access_token;
    
                SN.user = {
                    id: j.id_user || SN.user?.id || null,
                    name: SN.user?.name || cachedUser?.name || '',
                    loggedIn: true
                };
    
                // اگر سرور act فرستاد، نگه دار (برای پیام اکانت خاموش و ...)
                if (typeof j.act !== 'undefined') {
                    SN.user.act = j.act;
                }
    
                try {
                    localStorage.setItem('ha_rftk', j.refresh_token);
                    localStorage.setItem('ha_user', JSON.stringify({
                        id: SN.user.id,
                        name: SN.user.name
                    }));
                } catch (e) {}
                

                startHistoryFlushTimer();

                Promise.all([
                    Promise.resolve(SoftPullFavorites()),
                    SyncPlaylistsAfterLogin(),
                    SoftPullHistory()
                ]).then(() => {
                    maybeFlushHistory(true); // لوکال‌های pending از قبل را بفرست
                    if (onUpdate) onUpdate();
                }).catch(() => {
                    if (onUpdate) onUpdate();
                });
                return;
            }
        );
    }
    
    function GenerateRandomToken(bytes) {
        var arr = new Uint8Array(bytes);
        if (window.crypto && window.crypto.getRandomValues) {
            window.crypto.getRandomValues(arr);
        } else {
            for (var i = 0; i < bytes; i++) { arr[i] = Math.floor(Math.random() * 256); }
        }
        var hex = '';
        for (var j = 0; j < arr.length; j++) { hex += ('0' + arr[j].toString(16)).slice(-2); }
        return hex;
    }
    
    function PersistUserSession(userData, accessToken, refreshToken) {
        SN.user = {
            id: userData.id || null,
            name: userData.name || '',
            first_name: userData.first_name || '',
            last_name: userData.last_name || '',
            email: userData.email || '',
            act: userData.act,
            devices: Array.isArray(userData.devices) ? userData.devices : [],
            loggedIn: true
        };
        SN.accessToken = accessToken;
    
        try {
            localStorage.setItem('ha_user', JSON.stringify({
                id: SN.user.id,
                name: SN.user.name,
                first_name: SN.user.first_name,
                last_name: SN.user.last_name,
                email: SN.user.email
            }));
            localStorage.setItem('ha_rftk', refreshToken);
        } catch (e) {}
        
        startHistoryFlushTimer();
    }

    async function chp(u, callback) {

        if (u) {
            const stateObj = {Page: u, Url: u, pageTitle: ''};
            if (window.history && window.history.pushState) {
                window.history.pushState(stateObj, stateObj.Page, stateObj.Url);
            }
        }
    
        const LP = window.location.pathname;
        const LPS = LP.split("/");
        LPS[2] = LPS[2] ? LPS[2] : '';
        LPS[3] = LPS[3] ? LPS[3] : '';
        const QS = LPS[2] ? LPS[2] : '';
        
        // اگر max موبایل باز است → مینی
        if (MaxPly && MaxPly.classList.contains('translate-to-up')) {
            minimizeMaxToMini(); 
        }
        
        // ویدیو را از stage صفحه جدا کن، بعد محتوا را پاک کن
        beforePageClear();
        
        // Clear main content
        MCNT.innerHTML = '';
    
        const CHHD = (q, id) => {
    
            let thumb = '';
    
            if (q === 'a') {
    
                const artist = SN.artist ? SN.artist[id] : null;
    
                if (!artist) return;
    
                document.title =
                    artist.name +
                    (artist.name_fa ? ` | ${artist.name_fa}` : '');
    
                thumb = artist.meta && artist.meta.tmb
                    ? artist.meta.tmb
                    : '';
            }
    
            else if (q === 's') {
    
                const song = SN.song ? SN.song[id] : null;
    
                if (!song) return;
    
                // اسم اولین artist رو برای عنوان صفحه بگیر
                let artistName = '';
                let artistNameFa = '';
    
                if (
                    song.artists &&
                    Array.isArray(song.artists) &&
                    song.artists.length > 0
                ) {
    
                    const firstArtistId =
                        typeof song.artists[0] === 'object'
                            ? song.artists[0].id
                            : song.artists[0];
    
                    const firstArtist =
                        SN.artist ? SN.artist[firstArtistId] : null;
    
                    if (firstArtist) {
                        artistName = firstArtist.name || '';
                        artistNameFa = firstArtist.name_fa || '';
                    }
                }
    
                document.title =
                    song.name +
                    (artistName ? ` By ${artistName}` : '') +
                    (
                        song.name_fa
                            ? ` | آهنگ ${song.name_fa}${artistNameFa ? ` ${artistNameFa}` : ''}`
                            : ''
                    );
    
                thumb = song.meta && song.meta.tmb
                    ? song.meta.tmb
                    : '';
            }
    
            const oldLink = document.querySelector('link[as="image"]');
    
            if (oldLink && thumb) {
                oldLink.href = `${UT}${thumb}.jpg`;
                oldLink.as = 'image';
            }
        };
    
        const done = () => {
    
            const isDetailPage = /^\d+$/.test(LPS[2]);
    
            if (isDetailPage) {
    
                if (LPS[1] === 'artist') {
                    CHHD('a', LPS[2]);
    
                } else if (LPS[1] === 'song') {
                    CHHD('s', LPS[2]);
                }
            }
    
            if (callback) callback();
        };
    
        switch (LPS[1]) {
            
            case 'pair':
                try {
                    await CRPair(LPS[2] || '');
                } catch (e) {
                    console.error(e);
                }
                if (done) done();
                break;
    
            case 'search':
                CRSR(LPS[2], LPS[3], done);
                break;
    
            case 'setting':
                CRSA(LPS[2], LPS[3], done);
                break;
                
            case 'library':
                CRLP(LPS[2], LPS[3], done);
                break;
    
            default:
                CRHP(LPS[1], QS, done);
                break;
        }
    }

    function CRSA(q, a, callback) {
    
        q = (typeof q !== "undefined" && q !== '') ? q : 'account';
    
        
        var sec = OA(document.createElement("div"), {className: "section-carousel-container m-top-1"});
        MCNT.appendChild(sec);
        var tab = OA(document.createElement("div"), {className: "setting-tabs"});
        sec.appendChild(tab);
     
        var tabContent = OA(document.createElement("div"), {className: "setting-content m-top-1"});
        MCNT.appendChild(tabContent);
    
        var tabs = [];
        var panes = [];
    
        var tabTypes = [
            { key: 'account',  label: 'Profile',   icon:svg_usr, render: RenderAccountTab },
            { key: 'favorite', label: 'Favorites', icon:svg_fav, render: RenderFavoriteTab },
            { key: 'playlist', label: 'Playlists', icon:svg_pls, render: RenderPlaylistTab },
            { key: 'audio',    label: 'Audio',     icon:svg_ado, render: RenderAudioQualityTab },
            { key: 'video',    label: 'Video',     icon:svg_vdo, render: RenderVideoQualityTab }
        ];
    
        function createTab(type, active) {
            var item = OA(document.createElement("a"), {
                className: active ? "item active focusable" : "item focusable",
                href: "/setting/" + type.key,
            });
            
            //var tit = OA(document.createElement("img"), {className: "ratio ratio-1x1", src: thumb ? UT + thumb + ".jpg" : ""});
            var dci = OA(document.createElement("div"), {className: "icon", innerHTML: type.icon});
            var tis = OA(document.createElement("span"), {innerHTML: type.label});
            item.appendChild(dci);
            item.appendChild(tis);
            tab.appendChild(item);
            tabs.push(item);
            return item;
        }

        function createPane() {
            var pane = OA(document.createElement("div"), {className: "tab-pane"});
            pane.style.display = "none";
            tabContent.appendChild(pane);
            panes.push(pane);
            return pane;
        }
    
        function ActivateTab(index) {
            var j;
            for (j = 0; j < tabs.length; j++) {
                tabs[j].className = "item focusable";
                panes[j].style.display = "none";
            }
            tabs[index].className = "item active focusable";
            panes[index].style.display = "block";
        }
    
        var oldMCNT;
        var i;
        var activeIndex = 0;
    
        for (i = 0; i < tabTypes.length; i++) {
    
            var type = tabTypes[i];
            var isActive = (type.key === q);
    
            if (isActive) {
                activeIndex = tabs.length;
            }
    
            var tabEl = createTab(type, isActive);
            var paneEl = createPane();
    
            if (isActive) {
                paneEl.style.display = "block";
            }
    
            oldMCNT = MCNT;
            MCNT = paneEl;
    
            type.render(paneEl);
    
            MCNT = oldMCNT;
        }
    
        // فقط فوکوس/هاور پن رو عوض می‌کنه؛ خودِ ناوبری واقعی (تغییر URL) از طریق href/ADE انجام می‌شه
        for (var ti = 0; ti < tabs.length; ti++) {
    
            (function (index) {
    
                tabs[index].addEventListener("focus", function () {
                    ActivateTab(index);
                });
                
                tabs[index].addEventListener("click", function () {
                    ActivateTab(index);
                });
    
            })(ti);
        }

        CCRL(sec);
        ADE();

        var activeTab = tabs[activeIndex];

    
        if (callback) { callback(true); }
    }
    
    async function RenderAccountTab(pane) {
    
        const dcn = OA(document.createElement('div'), { className: 'user-content' });
        const dtl1 = OA(document.createElement('div'), { className: 'page-detail' });
        dcn.append(dtl1);
    
        // ========== مهمان: فرم ورود ==========
        if (!SN.user || !SN.user.loggedIn) {
            const passSection = OA(document.createElement('div'), {
                className: 'login-section login-password-section'
            });
            const ius = OA(document.createElement('input'), {
                className: 'input focusable',
                type: 'text',
                id: 'username',
                maxlength: 100,
                placeholder: 'Username or Email',
                autocorrect: 'off',
                autocapitalize: 'off',
                spellcheck: 'false'
            });
            const ips = OA(document.createElement('input'), {
                className: 'input focusable',
                type: 'password',
                id: 'password',
                maxlength: 100,
                placeholder: 'Password',
                autocorrect: 'off',
                autocapitalize: 'off',
                spellcheck: 'false'
            });
            const errorEl = OA(document.createElement('div'), {
                className: 'login-error',
                innerHTML: ''
            });
            errorEl.style.display = 'none';
    
            const submitBtn = OA(document.createElement('div'), {
                className: 'setting-item focusable',
                innerHTML: 'ورود'
            });
            submitBtn.addEventListener('click', () => {
                const username = ius.value.trim();
                const password = ips.value;
                if (username && password) {
                    AttemptPasswordLogin(username, password, errorEl, pane);
                    return;
                }
                errorEl.innerHTML = 'لطفاً نام کاربری و رمز عبور را وارد کنید';
                errorEl.style.display = 'block';
            });
    
            passSection.append(ius, ips, errorEl, submitBtn);
            dtl1.append(passSection);
    
            // ریست لوکال — حتی بدون لاگین
            const clearLocalBtn = OA(document.createElement('div'), {
                className: 'setting-item focusable m-top-2',
                innerHTML: 'پاک کردن دادهٔ محلی'
            });
            clearLocalBtn.addEventListener('click', () => {
                ResetLocalProfile();
                clearLocalBtn.innerHTML = 'پاک شد';
                setTimeout(() => { clearLocalBtn.innerHTML = 'پاک کردن دادهٔ محلی'; }, 800);
            });
            dtl1.append(clearLocalBtn);
    
            pane.append(dcn);
            return;
        }
    
        // ========== لاگین ==========
        LoadProfile();
    
        const displayName =
            [SN.user.first_name, SN.user.last_name].filter(Boolean).join(' ') ||
            SN.user.name ||
            '';
    
        dtl1.append(
            OA(document.createElement('div'), {
                className: 'setting-title',
                innerHTML: displayName
            })
        );
        if (SN.user.name) {
            dtl1.append(
                OA(document.createElement('div'), {
                    className: 'setting-subtitle',
                    innerHTML: '@' + SN.user.name
                })
            );
        }
        
        if (SN.user.email) {
            dtl1.append(
                OA(document.createElement('div'), {
                    className: 'setting-subtitle',
                    innerHTML: 'Email: ' + SN.user.email
                })
            );
        }
    
        // ---- ردیف اکشن‌های حساب: خروج | بروزرسانی همه | پاک کردن لوکال ----
        const accountActions = OA(document.createElement('div'), {
            className: 'account-actions m-top-1'
        });
    
        const logoutBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-3 m-top-1',
            innerHTML: 'خروج از حساب'
        });
        logoutBtn.addEventListener('click', () => {
            LogoutUser();
            if (pane) {
                RerenderAccountPane(pane);
            }
        });
    
        const syncAllBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-3 m-top-1',
            innerHTML: 'بروزرسانی همه'
        });
        syncAllBtn.addEventListener('click', async () => {
            syncAllBtn.innerHTML = '...';
            const ok = await ManualSyncAll();
            syncAllBtn.innerHTML = ok ? 'انجام شد' : 'خطا';
            setTimeout(() => {
                syncAllBtn.innerHTML = 'بروزرسانی همه';
                renderDevicesList(devicesBox);
                renderFavoriteCounts(favBox);
                renderPlaylistCounts(plBox);
                renderHistoryCounts(histBox);
            }, 800);
        });
    
        const clearLocalBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-3 m-top-1',
            innerHTML: 'پاک کردن دادهٔ محلی'
        });
        clearLocalBtn.addEventListener('click', () => {
            ResetLocalProfile();
            renderFavoriteCounts(favBox);
            renderPlaylistCounts(plBox);
            clearLocalBtn.innerHTML = 'پاک شد';
            setTimeout(() => { clearLocalBtn.innerHTML = 'پاک کردن دادهٔ محلی'; }, 800);
        });
    
        accountActions.append(logoutBtn, syncAllBtn, clearLocalBtn);
        dtl1.append(accountActions);
        pane.append(dcn);
    
        // ========== سکشن دستگاه‌ها ==========
        const secDevices = OA(document.createElement('div'), { className: 'section m-top-1' });
        secDevices.append(
            OA(document.createElement('div'), {
                className: 'section-header',
                // title
            })
        );
        const devHeader = OA(document.createElement('div'), { className: 'section-header' });
        devHeader.append(
            OA(document.createElement('div'), {
                className: 'title',
                innerHTML: 'دستگاه‌های فعال'
            })
        );
        const devicesBox = OA(document.createElement('div'), { className: 'devices-list content' });
        secDevices.append(devHeader, devicesBox);
    
        renderDevicesList(devicesBox);
        refreshDevicesList(devicesBox);
        
        const Brow = OA(document.createElement('div'), {
            className: 'setting-row'
        });
        
        const refreshDevBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-4 m-top-1',
            innerHTML: 'بروزرسانی دستگاه‌ها'
        });
        refreshDevBtn.addEventListener('click', () => {
            refreshDevBtn.innerHTML = '...';
            refreshDevicesList(devicesBox, () => {
                refreshDevBtn.innerHTML = 'بروزرسانی دستگاه‌ها';
            });
        });
        
        const addDevBtn = OA(document.createElement('a'), {
            className: 'btn btn-sm btn-model-4 m-top-1',
            innerHTML: 'Add a Device',
            href: '/pair'
        });

        
        Brow.append(refreshDevBtn, addDevBtn);
        
        secDevices.append(Brow);
        pane.append(secDevices);
    
        // ========== سکشن فاوریت (فقط تعداد) ==========
        await HydrateProfileItems();
    
        const secFavorites = OA(document.createElement('div'), { className: 'section m-top-1' });
        const favHeader = OA(document.createElement('div'), { className: 'section-header' });
        favHeader.append(
            OA(document.createElement('div'), { className: 'title', innerHTML: 'Favorites' }),
            OA(document.createElement('a'), {className: 'see-all', innerHTML: 'See All', href: '/library' })
        );
        
         
               
        const favBox = OA(document.createElement('div'), { className: 'counts-list' });
        secFavorites.append(favHeader, favBox);
        renderFavoriteCounts(favBox);
    
        const syncFavBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-4 m-top-1',
            innerHTML: 'همگام‌سازی علاقه‌مندی‌ها'
        });
        syncFavBtn.addEventListener('click', async () => {
            syncFavBtn.innerHTML = '...';
            const ok = await ManualSyncFavorites();
            syncFavBtn.innerHTML = ok ? 'انجام شد' : 'خطا';
            setTimeout(() => {
                syncFavBtn.innerHTML = 'همگام‌سازی علاقه‌مندی‌ها';
                renderFavoriteCounts(favBox);
            }, 800);
        });
        secFavorites.append(syncFavBtn);
        pane.append(secFavorites);
    
        // ========== سکشن پلی‌لیست (تعداد) ==========
        const secPlaylists = OA(document.createElement('div'), { className: 'section m-top-1' });
        const plHeader = OA(document.createElement('div'), { className: 'section-header' });
        plHeader.append(
            OA(document.createElement('div'), { className: 'title', innerHTML: 'Playlists' }),
            OA(document.createElement('a'), {className: 'see-all', innerHTML: 'See All', href: '/library/playlist' })
        );
        const plBox = OA(document.createElement('div'), { className: 'counts-list' });
        secPlaylists.append(plHeader, plBox);
        renderPlaylistCounts(plBox);
    
        const BProw = OA(document.createElement('div'), {
            className: 'setting-row'
        });
        
        const syncPlBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-4 m-top-1',
            innerHTML: 'همگام‌سازی پلی‌لیست‌ها'
        });
        syncPlBtn.addEventListener('click', async () => {
            syncPlBtn.innerHTML = '...';
            const ok = await ManualSyncPlaylists();
            syncPlBtn.innerHTML = ok ? 'انجام شد' : 'خطا';
            setTimeout(() => {
                syncPlBtn.innerHTML = 'همگام‌سازی پلی‌لیست‌ها';
                renderPlaylistCounts(plBox);
            }, 800);
        });
        
        const addPlBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-4 m-top-1',
            innerHTML: 'New Playlist'
        });
        addPlBtn.addEventListener('click', () => {
            CRBW(null, 'playlist', 'add', { fresh: true });
        });
        
        BProw.append(syncPlBtn, addPlBtn);
        
        secPlaylists.append(BProw);
        pane.append(secPlaylists);
    
        // ==========شن هیستوری (جای خالی برای بعد) ==========
        const secHistory = OA(document.createElement('div'), { className: 'section m-top-1' });
        const histHeader = OA(document.createElement('div'), { className: 'section-header' });
        histHeader.append(
            OA(document.createElement('div'), { className: 'title', innerHTML: 'History' }),
            OA(document.createElement('a'), {
                className: 'see-all',
                innerHTML: 'See All',
                href: '/library' // یا /library و اسکرول؛ تک‌type: /library/history/song
            })
        );
        const histBox = OA(document.createElement('div'), { className: 'counts-list' });
        secHistory.append(histHeader, histBox);
        renderHistoryCounts(histBox);
        
        const syncHistBtn = OA(document.createElement('div'), {
            className: 'btn btn-sm btn-model-4 m-top-1',
            innerHTML: 'همگام‌سازی تاریخچه'
        });
        syncHistBtn.addEventListener('click', async () => {
            syncHistBtn.innerHTML = '...';
            const ok = await ManualSyncHistory();
            syncHistBtn.innerHTML = ok ? 'انجام شد' : 'خطا';
            setTimeout(() => {
                syncHistBtn.innerHTML = 'همگام‌سازی تاریخچه';
                renderHistoryCounts(histBox);
            }, 800);
        });
        secHistory.append(syncHistBtn);
        pane.append(secHistory);
    }
    
    /**
     * @param {HTMLElement} host
     * @param {{ mode?: 'login'|'register', onSuccess?: Function }} opts
     */
    function RenderAuthForm(host, opts) {
        opts = opts || {};
        let mode = opts.mode || 'login';
    
        const root = OA(document.createElement('div'), { className: 'auth-form' });
        const errorEl = OA(document.createElement('div'), {
            className: 'login-error',
            style: 'display:none'
        });
    
        const userIn = OA(document.createElement('input'), {
            className: 'input focusable',
            type: 'text',
            placeholder: 'Username or Email',
            maxlength: 100
        });
        const passIn = OA(document.createElement('input'), {
            className: 'input focusable',
            type: 'password',
            placeholder: 'Password',
            maxlength: 100
        });
        // برای register بعداً email / name هم اضافه کن
        const pass2 = OA(document.createElement('input'), {
            className: 'input focusable',
            type: 'password',
            placeholder: 'Confirm password',
            maxlength: 100,
            style: 'display:none'
        });
    
        const submit = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: 'ورود'
        });
        const switchBtn = OA(document.createElement('div'), {
            className: 'btn btn-model-3',
            innerHTML: 'ساخت حساب'
        });
    
        function applyMode() {
            if (mode === 'login') {
                pass2.style.display = 'none';
                submit.innerHTML = 'ورود';
                switchBtn.innerHTML = 'ساخت حساب';
            } else {
                pass2.style.display = '';
                submit.innerHTML = 'ثبت‌نام';
                switchBtn.innerHTML = 'قبلاً حساب دارم';
            }
        }
    
        switchBtn.addEventListener('click', () => {
            mode = mode === 'login' ? 'register' : 'login';
            errorEl.style.display = 'none';
            applyMode();
            if (opts.onModeChange) opts.onModeChange(mode);
        });
    
        submit.addEventListener('click', async () => {
            errorEl.style.display = 'none';
            if (mode === 'login') {
                await AttemptPasswordLogin(
                    userIn.value.trim(),
                    passIn.value,
                    errorEl,
                    null,
                    opts.onSuccess
                );
            } else {
                errorEl.innerHTML = 'ثبت‌نام به‌زودی';
                errorEl.style.display = 'block';
                // بعداً AttemptRegister
               //  await AttemptRegister(/* ... */, errorEl, async () => {
               //     if (opts.onSuccess) await opts.onSuccess();
               // });
            }
        });
    
        applyMode();
        root.append(errorEl, userIn, passIn, pass2, submit, switchBtn);
        host.append(root);
    }
    
    function buildPairCodeInput(initialCode) {
        const wrap = OA(document.createElement('div'), { className: 'pair-code-wrap' });
        const cells = [];
        let isError = false;
    
        const hidden = OA(document.createElement('input'), {
            type: 'text',
            inputMode: 'numeric',
            autocomplete: 'one-time-code',
            maxlength: 6,
            className: 'pair-code-hidden',
            value: (initialCode || '').replace(/\D/g, '').slice(0, 6)
        });
    
        const row = OA(document.createElement('div'), { className: 'pair-code-cells' });
        for (let i = 0; i < 6; i++) {
            const cell = OA(document.createElement('div'), {
                className: 'pair-code-cell',
                innerHTML: hidden.value[i] || '–'
            });
            cells.push(cell);
            row.append(cell);
        }
    
        function paint() {
            const v = hidden.value.replace(/\D/g, '').slice(0, 6);
            hidden.value = v;
            cells.forEach((c, i) => {
                c.innerHTML = v[i] || '–';
                c.classList.toggle('filled', !!v[i]);
                c.classList.toggle('pair-code-error', isError && !!v[i]);
            });
            wrap.classList.toggle('has-error', isError);
        }
    
        wrap.addEventListener('click', () => hidden.focus());
        hidden.addEventListener('input', () => {
            if (isError) {
                isError = false; // با تایپ جدید، قرمز برداشته شود
            }
            paint();
        });
        paint();
    
        wrap.append(row, hidden);
    
        return {
            wrap,
            getCode: () => hidden.value,
            setCode: (c) => {
                hidden.value = String(c || '').replace(/\D/g, '').slice(0, 6);
                paint();
            },
            setError: (on) => {
                isError = !!on;
                paint();
            }
        };
    }

    async function CRPair(prefillCode) {
        MCNT.innerHTML = '';
    
        const box = OA(document.createElement('div'), { className: 'pair-page' });
        MCNT.append(box);
        ADE();
    
        const initial = String(prefillCode || '').replace(/\D/g, '').slice(0, 6);
    
        // لایهٔ ثابت پیر (کد)
        const pairBlock = OA(document.createElement('div'), { className: 'pair-block' });
        const authHost = OA(document.createElement('div'), { className: 'pair-auth-host' });
    
        pairBlock.append(
            OA(document.createElement('h1'), { innerHTML: 'Pair your hamrahang account' }),
            OA(document.createElement('p'), {
                className: 'pair-hint',
                innerHTML: 'Enter the 6-digit code that is shown on the device'
            })
        );
    
        const codeUi = buildPairCodeInput(initial);
        const err = OA(document.createElement('div'), {
            className: 'pair-error',
            style: 'display:none'
        });
        const nextBtn = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: 'Next'
        });
    
        pairBlock.append(codeUi.wrap, err, nextBtn);
        box.append(pairBlock, authHost);
    
        function showLoginUnderPair() {
            pairBlock.style.display = '';
            authHost.innerHTML = '';
            RenderAuthForm(authHost, {
                mode: 'login',
                onModeChange: (m) => {
                    if (m === 'register') {
                        // ثبت‌نام زمان‌بر → پیر را مخفی کن
                        pairBlock.style.display = 'none';
                    } else {
                        pairBlock.style.display = '';
                    }
                },
                onSuccess: () => {
                    const c = codeUi.getCode();
                    if (c.length !== 6) {
                        err.innerHTML = 'کد ۶ رقمی را کامل وارد کنید';
                        err.style.display = 'block';
                        return;
                    }
                    authHost.innerHTML = '';
                    finishPair(c, err, nextBtn, codeUi);
                }
            });
        }
    
        nextBtn.addEventListener('click', () => {
            const c = codeUi.getCode();
            if (c.length !== 6) {
                err.innerHTML = 'کد ۶ رقمی را کامل وارد کنید';
                err.style.display = 'block';
                return;
            }
            err.style.display = 'none';
    
            if (!SN.user?.loggedIn || !SN.accessToken) {
                showLoginUnderPair();
                return;
            }
            finishPair(c, err, nextBtn, codeUi);
        });
    
        // کد از URL
        if (initial.length === 6) {
            if (SN.user?.loggedIn && SN.accessToken) {
                nextBtn.innerHTML = '...';
                finishPair(initial, err, nextBtn, codeUi);
            } else {
                showLoginUnderPair();
            }
        }
    }
    
    async function finishPair(code, errEl, nextBtn, codeUi) {
        if (!SN.user?.loggedIn || !SN.accessToken) {
            errEl.innerHTML = 'ابتدا وارد حساب شوید';
            errEl.style.display = 'block';
            if (nextBtn) nextBtn.innerHTML = 'Next';
            return;
        }
    
        if (nextBtn) nextBtn.innerHTML = '...';
    
        gjl({ a: 'confirm_device', code: String(code) }, function (success, j) {
            if (nextBtn) nextBtn.innerHTML = 'Next';
            console.log('confirm_device', success, j);
    
            function markCodeBad() {
                if (codeUi && typeof codeUi.setError === 'function') {
                    codeUi.setError(true);
                    codeUi.setCode(code); // همان کد اشتباه بماند
                }
                const cells = document.querySelectorAll('.pair-code-cell');
                cells.forEach((c) => c.classList.add('pair-code-error'));
            }
    
            function clearCodeBad() {
                if (codeUi && typeof codeUi.setError === 'function') {
                    codeUi.setError(false);
                }
                document.querySelectorAll('.pair-code-cell').forEach((c) => {
                    c.classList.remove('pair-code-error');
                });
            }
    
            if (j && j.error) {
                if (j.error === 'unauthorized') {
                    errEl.innerHTML = 'وارد حساب شوید / نشست منقضی';
                } else if (j.error === 'invalid_or_expired_code') {
                    errEl.innerHTML = 'کد نامعتبر یا منقضی است';
                    markCodeBad();
                    // chp('/pair') نزن
                } else {
                    errEl.innerHTML = String(j.error);
                    markCodeBad();
                }
                errEl.style.display = 'block';
                return;
            }
    
            if (!success || !j) {
                errEl.innerHTML = 'خطا در ارتباط با سرور';
                errEl.style.display = 'block';
                return;
            }
    
            if (j.ok) {
                clearCodeBad();
                if (nextBtn) nextBtn.style.display = 'none';
            
                errEl.style.display = 'block';
                errEl.style.color = 'green';
                errEl.innerHTML = 'دستگاه با موفقیت متصل شد';
            
                // دکمه‌های انتخاب
                let actions = errEl.parentNode && errEl.parentNode.querySelector('.pair-success-actions');
                if (!actions) {
                    actions = OA(document.createElement('div'), { className: 'pair-success-actions' });
                    errEl.insertAdjacentElement('afterend', actions);
                }
                actions.innerHTML = '';
            
                const btnHome = OA(document.createElement('div'), {
                    className: 'btn btn-model-3',
                    innerHTML: 'Home'
                });

                const btnAccount = OA(document.createElement('div'), {
                    className: 'btn btn-model-3',
                    innerHTML: 'Account'
                });
            
                btnHome.addEventListener('click', () => chp('/'));
                btnAccount.addEventListener('click', () => chp('/setting/account'));
            
                actions.append(btnHome, btnAccount);
            }
        });
    }
    
    function countByItemTypes(mapOrLists) {
        // mapOrLists = SN.profile.favorite  → { song: [...], artist: [...] }
        const rows = [];
        for (const t of ITEM_TYPES) {
            const list = mapOrLists?.[t];
            const n = Array.isArray(list) ? list.length : 0;
            if (n > 0) rows.push({ type: t, count: n });
        }
        return rows;
    }

    function renderFavoriteCounts(container) {
        LoadProfile();
        const rows = countByItemTypes(SN.profile.favorite);
        renderTypeCountList(container, rows, 'هنوز علاقه‌مندی ندارید');
    }
    
    function renderPlaylistCounts(container) {
        LoadProfile();
        const ids = SN.profile.playlist || [];
        const n = ids.length;
        container.innerHTML = '';
        if (!n) {
            container.append(
                OA(document.createElement('div'), {
                    className: 'text2',
                    innerHTML: 'هنوز پلی‌لیستی ندارید'
                })
            );
            return;
        }
        const row = OA(document.createElement('div'), {
            className: 'setting-row type-count-row'
        });
        row.append(
            OA(document.createElement('span'), {
                className: 'type-name',
                innerHTML: 'playlist'
            }),
            OA(document.createElement('span'), {
                className: 'type-count',
                innerHTML: String(n)
            })
        );
        container.append(row);
    }
    
    /** از SN.profile.history → [{ type, count }, ...] فقط typeهایی که count > 0 */
    function countHistoryByType() {
        LoadProfile();
        const list = Array.isArray(SN.profile.history) ? SN.profile.history : [];
        const map = {};
    
        for (let i = 0; i < PLAYABLE_TYPES.length; i++) {
            map[PLAYABLE_TYPES[i]] = 0;
        }
    
        for (let i = 0; i < list.length; i++) {
            const row = list[i];
            if (!row || row.type == null) continue;
            const t = String(row.type);
            if (map[t] === undefined) continue;
            map[t]++;
        }
    
        const rows = [];
        for (let i = 0; i < PLAYABLE_TYPES.length; i++) {
            const t = PLAYABLE_TYPES[i];
            if (map[t] > 0) {
                rows.push({ type: t, count: map[t] });
            }
        }
        return rows;
    }

    function renderHistoryCounts(container) {
        if (!container) return;
        const rows = countHistoryByType();
        renderTypeCountList(container, rows, 'هنوز هیستوری ندارید');
    }
    
    /** لیست ساده: type + تعداد (فاوریت / هیستوری / …) */
    function renderTypeCountList(container, rows, emptyText) {
        container.innerHTML = '';
        if (!rows || !rows.length) {
            container.append(
                OA(document.createElement('div'), {
                    className: 'text2',
                    innerHTML: emptyText || 'موردی نیست'
                })
            );
            return;
        }
        rows.forEach(({ type, count }) => {
            const row = OA(document.createElement('div'), {
                className: 'setting-row type-count-row'
            });
            row.append(
                OA(document.createElement('span'), {
                    className: 'type-name',
                    innerHTML: type
                }),
                OA(document.createElement('span'), {
                    className: 'type-count',
                    innerHTML: String(count)
                })
            );
            container.append(row);
        });
    }
    
    
    /** merge سرور → لوکال (at بزرگ‌تر می‌ماند؛ سقف per type) */
    function mergeServerHistoryIntoLocal(serverList) {
        LoadProfile();
        if (!Array.isArray(SN.profile.history)) SN.profile.history = [];
        if (!Array.isArray(serverList)) return;
    
        const map = new Map();
        function key(id, type) {
            return String(type) + ':' + String(id);
        }
    
        SN.profile.history.forEach((row) => {
            if (!row || row.id == null) return;
            map.set(key(row.id, row.type || 'song'), {
                id: row.id,
                type: row.type || 'song',
                at: row.at || 0
            });
        });
    
        serverList.forEach((row) => {
            if (!row || row.id == null) return;
            const t = row.type || 'song';
            if (PLAYABLE_TYPES.indexOf(t) === -1) return;
            const k = key(row.id, t);
            const at = row.at || 0;
            const prev = map.get(k);
            if (!prev || at >= (prev.at || 0)) {
                map.set(k, { id: row.id, type: t, at: at });
            }
        });
    
        let merged = Array.from(map.values());
        merged.sort((a, b) => (b.at || 0) - (a.at || 0));
    
        // سقف ۵۰ per type
        const counts = {};
        merged = merged.filter((row) => {
            const t = row.type;
            counts[t] = (counts[t] || 0) + 1;
            return counts[t] <= HISTORY_MAX;
        });
    
        SN.profile.history = merged;
        SaveProfile();
    }
    
    async function fetchServerHistory() {
        if (!SN.user?.loggedIn || !SN.accessToken) return null;
        const j = await gja({ a: 'get_history' });
        if (!j || j.error || !Array.isArray(j.history)) return null;
        return j.history;
    }
    
    /** دکمه دستی / بخشی از ManualSyncAll */
    async function ManualSyncHistory() {
        if (!SN.user?.loggedIn || !SN.accessToken) return false;
    
        const serverList = await fetchServerHistory();
        if (serverList) {
            mergeServerHistoryIntoLocal(serverList);
        }
    
        // کل لوکال را push کن (سرور upsert می‌کند)
        LoadProfile();
        const items = (SN.profile.history || []).map((row) => ({
            id: row.id,
            type: row.type || 'song',
            at: row.at || Math.floor(Date.now() / 1000)
        }));
    
        if (items.length) {
            const j = await gja({ a: 'sync_history', items: items });
            if (!j || j.error) return false;
            SN._pendingHistory = [];
            SN._lastHistoryFlushAt = Date.now();
        }
    
        return true;
    }
    
    async function SoftPullHistory() {
        const serverList = await fetchServerHistory();
        if (serverList) mergeServerHistoryIntoLocal(serverList);
    }
    
    function ResetLocalProfile() {
        try {
            localStorage.removeItem(PROFILE_KEY);
        } catch (e) {}
    
        SN.profile = null;
        if (SN.playlist) {
            // فقط لوکال ۱–۹۹ را از SN بردار؛ سروری‌ها با pull دوباره می‌آیند
            Object.keys(SN.playlist).forEach((id) => {
                const n = Number(id);
                if (n >= 1 && n <= LOCAL_PLAYLIST_MAX) delete SN.playlist[id];
            });
        }
        LoadProfile();
        SaveProfile();
    }

    function renderDevicesList(container) {
        container.innerHTML = '';
    
        const devices = (SN.user && SN.user.devices) ? SN.user.devices : [];
        if (!devices.length) {
            container.append(
                OA(document.createElement('div'), {
                    className: 'setting-empty',
                    innerHTML: 'دستگاهی یافت نشد'
                })
            );
            return;
        }
    
        const currentDid = GetOrCreateDeviceId();
    
        devices.forEach(function (dev) {
            const isCurrent = dev.is_current || dev.did === currentDid;
    
            const row = OA(document.createElement('div'), {
                className: 'setting-row type-count-row' + (isCurrent ? ' is-current' : '')
            });
    
            const info = OA(document.createElement('div'), { className: 'device-info' });
            const title = OA(document.createElement('div'), {
                className: 'device-title',
                innerHTML: (dev.device_label || dev.device_type || 'Device') +
                    (isCurrent ? ' (این دستگاه)' : '')
            });
            const meta = OA(document.createElement('div'), {
                className: 'device-meta',
                innerHTML: formatDeviceMeta(dev)
            });
            info.append(title, meta);
            row.append(info);
    
            if (!isCurrent) {
                const removeBtn = OA(document.createElement('div'), {
                    className: 'btn btn-sm btn-model-3',
                    innerHTML: 'خروج'
                });
                removeBtn.addEventListener('click', function () {
                    revokeDevice(dev.did, container);
                });
                row.append(removeBtn);
            }
    
            container.append(row);
        });
    }
    
    function formatDeviceMeta(dev) {
        const parts = [];
        if (dev.device_type) parts.push(dev.device_type);
        if (dev.ip) parts.push(dev.ip);
        if (dev.last_active) {
            const d = new Date(dev.last_active * 1000);
            parts.push(d.toLocaleDateString('fa-IR') + ' ' + d.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' }));
        }
        return parts.join(' · ');
    }
    
    function refreshDevicesList(container, done) {
        const did = GetOrCreateDeviceId();
    
        gjl({ a: 'list_devices', did: did }, function (success, j) {
            if (success && j && Array.isArray(j.devices) && SN.user) {
                SN.user.devices = j.devices;
                renderDevicesList(container);
            }
            if (done) done(success, j);
        });
    }
    
    function revokeDevice(targetDid, container) {
        gjl({ a: 'revoke_device', did: targetDid }, function (success, j) {
            if (!success || !j || !j.ok) {
                console.warn('revoke failed', j);
                return;
            }
            // از لیست محلی حذف کن
            if (SN.user && Array.isArray(SN.user.devices)) {
                SN.user.devices = SN.user.devices.filter(d => d.did !== targetDid);
            }
            renderDevicesList(container);
        });
    }
    
    async function AttemptPasswordLogin(username, password, errorEl, pane, onSuccess) {
        errorEl.style.display = 'none';
    
        const did = GetOrCreateDeviceId();
    
        gjl(
            { a: 'login_password', username, password, did, device_type: 'webapp' },
            async function (success, j) {
                if (!success || !j) {
                    errorEl.innerHTML = 'خطا در ارتباط با سرور';
                    errorEl.style.display = 'block';
                    return;
                }
    
                if (j.error == 'account_disabled') {
                    errorEl.innerHTML = 'دسترسی شما مسدود گردید، لطفا با پشتیبانی تماس بگیرید';
                    errorEl.style.display = 'block';
                    return;
                }
    
                if (!j.access_token) {
                    errorEl.innerHTML = 'نام کاربری یا رمز عبور اشتباه است';
                    errorEl.style.display = 'block';
                    return;
                }
    
                PersistUserSession({
                    id: j.id_user,
                    name: j.user_name || '',
                    first_name: j.first_name || '',
                    last_name: j.last_name || '',
                    email: j.email || '',
                    act: j.act,
                    devices: j.devices || []
                }, j.access_token, j.refresh_token);
                
                maybeFlushHistory(true);
                
                if (pane) {
                    RerenderAccountPane(pane);
                }
                if (typeof onSuccess === 'function') {
                    await onSuccess();
                }

                const serverFav = await fetchServerFavorites();
                if (serverFav) mergeServerFavoritesIntoLocal(serverFav);
    
                await SyncPlaylistsAfterLogin(); // فقط get_playlists
    
                if (hasLocalDataToSync()) {
                    showSyncPrompt(async (didSync) => {
                        if (didSync) {
                            await ManualSyncFavorites();
                            await ManualSyncPlaylists();
                        }
                        await HydrateProfileItems();
                        if (pane) {
                            RerenderAccountPane(pane);
                        }
                        
                        if (typeof onSuccess === 'function') {
                            await onSuccess();
                        }

                    });
                } else {
                    await HydrateProfileItems();
                    if (pane) {
                        RerenderAccountPane(pane);
                    }
                    
                    if (typeof onSuccess === 'function') {
                        await onSuccess();
                    }
                }
            }
        );
    }
    
    function RerenderAccountPane(pane) {
        pane.innerHTML = "";
        RenderAccountTab(pane);
        ADE();
    }

    function LogoutUser() {

        var refreshToken = null;
        try {
            refreshToken = localStorage.getItem('ha_rftk');
        } catch (e) {}
    
        if (refreshToken) {
            gjl({ a: 'logout', refresh_token: refreshToken, did: GetOrCreateDeviceId() }, function () {});
        }
    
        SN.user = { loggedIn: false, id: null, name: null, fv: { sng: [], art: [], alb: [] }, playlists: [] };
        SN.accessToken = null;
    
        try {
            localStorage.removeItem('ha_rftk');
            localStorage.removeItem('ha_user');
        } catch (e) {}
        
        stopHistoryFlushTimer();
        SN._pendingHistory = [];
    }
    
    function LoadUserSession(callback) {

        var userId = null;
        try {
            userId = localStorage.getItem('ha_user_id');
        } catch (e) {}
    
        if (!userId) {
            SN.user = { loggedIn: false, id: null, name: null, fv: { sng: [], art: [], alb: [] }, playlists: [] };
            if (callback) { callback(false); }
            return;
        }
    
        LoadUserFromAPI(userId, callback);
    }
    
    async function RenderFavoriteTab(pane) {
        LoadProfile();
    
        // ManualSync
        if (SN.user?.loggedIn) {
            const syncBtn = OA(document.createElement('div'), {
                className: 'setting-item focusable',
                innerHTML: 'همگام‌سازی با حساب'
            });
    
            syncBtn.addEventListener('click', async () => {
                syncBtn.innerHTML = '...';
                const ok = await ManualSyncFavorites();
                syncBtn.innerHTML = ok ? 'همگام‌سازی انجام شد' : 'خطا در همگام‌سازی';
                setTimeout(() => {
                    syncBtn.innerHTML = 'همگام‌سازی با حساب';
                    listBox.innerHTML = '';
                    renderFavoriteSections(listBox);
                }, 800);
            });
    
            pane.append(syncBtn);
        }
    
        await HydrateProfileItems();
    
        const listBox = OA(document.createElement('div'), { className: 'favorite-tab-list' });
        pane.append(listBox);
        renderFavoriteSections(listBox);
    }
    
    function renderFavoriteSections(container) {
        LoadProfile();
    
        const sections = [
            { type: 'song',        label: 'Songs',        render: (ids) => CRSC(ids, 'song') },
            { type: 'artist',      label: 'Artists',      render: (ids) => CRSC(ids, 'artist') },
            { type: 'album',       label: 'Albums',       render: (ids) => CRGC(ids, 'album') },
            { type: 'musicvideo',  label: 'Music Videos', render: (ids) => CRGC(ids, 'musicvideo') },
            { type: 'video',       label: 'Videos',       render: (ids) => CRGC(ids, 'video') },
            { type: 'podcast',     label: 'Podcasts',     render: (ids) => CRGC(ids, 'podcast') },
            { type: 'playlist',    label: 'Playlists',    render: (ids) => CRGC(ids, 'playlist') },
        ];
    
        // برای رندر داخل container، موقتاً MCNT را عوض می‌کنیم
        const oldMCNT = MCNT;
        MCNT = container;
    
        for (const sec of sections) {
            const ids = SN.profile.favorite[sec.type];
            if (!ids || !ids.length) continue;
    
            const hed = OA(document.createElement('div'), { className: 'section-header m-top-3' });
            const ht = OA(document.createElement('h2'), { className: 'title', innerHTML: sec.label });
            hed.append(ht);
            container.append(hed);
    
            sec.render(ids);
        }
    
        MCNT = oldMCNT;
        ADE();
    }
    
    function ShowFavoriteSyncSheet(count, localOnly) {
        // اگر از قبل باز است، دوباره نساز
        if (document.querySelector('.sync-sheet-wrapper')) return;
    
        const dbw = OA(document.createElement('div'), { className: 'bottom-sheet-wrapper sync-sheet-wrapper' });
        const dbc = OA(document.createElement('div'), { className: 'bottom-sheet-container' });
    
        const hed = OA(document.createElement('div'), { className: 'bottom-sheet-header' });
        const title = OA(document.createElement('div'), {
            className: 'texts',
            innerHTML: `<div>همگام‌سازی علاقه‌مندی‌ها</div>
                        <div>${count} مورد فقط روی این دستگاه است</div>`
        });
        hed.append(title);
    
        const cnt = OA(document.createElement('div'), { className: 'bottom-sheet-content' });
        const msg = OA(document.createElement('div'), {
            className: 'bottom-sheet-menu',
            innerHTML: `<div class="item" style="pointer-events:none">
                آیا می‌خواهید این موارد به حساب شما اضافه شوند؟
                <br><small>چیزی حذف نمی‌شود؛ فقط اضافه می‌شود.</small>
            </div>`
        });
        cnt.append(msg);
    
        const syncBtn = OA(document.createElement('div'), {
            className: 'setting-item focusable',
            innerHTML: 'همگام‌سازی با حساب'
        });
    
        const btc = OA(document.createElement('div'), {
            className: 'bottom-sheet-close',
            innerHTML: 'Close'
        });
    
        dbc.append(hed, cnt, syncBtn, btc);
        dbw.append(dbc);
        MCLN.append(dbw);
    
        function closeSheet() {
            dbw.classList.add('translate-to-down');
            setTimeout(() => dbw.remove(), 500);
        }
    
        btc.addEventListener('click', closeSheet);
    
        syncBtn.addEventListener('click', async () => {
            syncBtn.innerHTML = '...';
            const j = await pushLocalFavoritesToServer(localOnly);
            syncBtn.innerHTML = j ? 'انجام شد' : 'خطا — دوباره تلاش کنید';
            if (j) {
                setTimeout(closeSheet, 600);
            } else {
                syncBtn.innerHTML = 'همگام‌سازی با حساب';
            }
        });
    }
    
    async function RenderPlaylistTab(pane) {
        LoadProfile();

        // ---- ساخت پلی‌لیست جدید (مهمان یا لاگین؛ فعلاً فقط لوکال) ----
        const createBox = OA(document.createElement('div'), { className: 'playlist-create m-top-1' });
    
        const nameInput = OA(document.createElement('input'), {
            className: 'input focusable',
            type: 'text',
            maxlength: 100,
            placeholder: 'نام پلی‌لیست'
        });
    
        const createBtn = OA(document.createElement('div'), {
            className: 'btn btn-model-1 focusable',
            innerHTML: 'ساخت پلی‌لیست'
        });
    
        createBtn.addEventListener('click', () => {
            const name = nameInput.value.trim();
            if (!name) return;
    
            const pl = createPlaylist(name);
            if (!pl) {
                createBtn.innerHTML = 'سقف ۹۹ پر است';
                return;
            }
    
            nameInput.value = '';
            listBox.innerHTML = '';
            renderPlaylistList(listBox);
            createBtn.innerHTML = 'ساخته شد';
            setTimeout(() => { createBtn.innerHTML = 'ساخت پلی‌لیست'; }, 800);
        });
    
        createBox.append(nameInput, createBtn);
        pane.append(createBox);
    
        // ManualSync
        if (SN.user?.loggedIn) {
            const syncBtn = OA(document.createElement('div'), {
                className: 'setting-item focusable',
                innerHTML: 'همگام‌سازی با حساب'
            });
        
            syncBtn.addEventListener('click', async () => {
                syncBtn.innerHTML = '...';
                const ok = await ManualSyncPlaylists();
                syncBtn.innerHTML = ok ? 'همگام‌سازی انجام شد' : 'خطا در همگام‌سازی';
                setTimeout(() => {
                    syncBtn.innerHTML = 'همگام‌سازی با حساب';
                    listBox.innerHTML = '';
                    renderPlaylistList(listBox);
                }, 800);
            });
        
            pane.append(syncBtn);
        }
        
        await HydrateProfileItems();
        
        const listBox = OA(document.createElement('div'), { className: 'playlist-tab-list m-top-1' });
        pane.append(listBox);
        renderPlaylistList(listBox);
    }
    
    function renderPlaylistList(container) {
        LoadProfile();
        container.innerHTML = '';
    
        const ids = SN.profile.playlist || [];
    
        if (!ids.length) {
            container.append(
                OA(document.createElement('div'), {
                    className: 'text2',
                    innerHTML: 'هنوز پلی‌لیستی نساخته‌اید'
                })
            );
            return;
        }
    
        const ready = ids.filter((id) => SN.playlist && SN.playlist[id]);
        if (!ready.length) {
            container.append(
                OA(document.createElement('div'), {
                    className: 'text2',
                    innerHTML: 'لیست خالی است'
                })
            );
            return;
        }
    
        const oldMCNT = MCNT;
        MCNT = container;
        CRSC(ready, 'playlist');
        MCNT = oldMCNT;
        ADE();
    }
    
    function RenderAudioQualityTab(pane) {
        const currentQuality = GetSetting('audioQuality') || 'medium';
        const list = CreateQualityList(currentQuality, (value) => {
            SetSetting('audioQuality', value);
        });
        pane.appendChild(list);
    }
    
    function RenderVideoQualityTab(pane) {
        const currentQuality = GetSetting('videoQuality') || 'medium';
        const list = CreateQualityList(currentQuality, (value) => {
            SetSetting('videoQuality', value);
        });
        pane.appendChild(list);
    }
    
    function CreateQualityList(currentValue, onSelect) {
        const options = [
            { value: 'high', label: 'Highest' },
            { value: 'medium', label: 'Medium' },
            { value: 'low', label: 'Lowest' }
        ];
    
        const container = OA(document.createElement('div'), { className: 'quality-list' });
        const items = [];
    
        options.forEach((opt) => {
            const isActive = opt.value === currentValue;
            const item = OA(document.createElement('div'), {
                className: 'quality-item focusable' + (isActive ? ' active' : ''),
                innerHTML: opt.label
            });
            item._value = opt.value;
            items.push(item);
    
            const selectThis = () => {
                items.forEach((el) => el.classList.remove('active'));
                item.classList.add('active');
                if (onSelect) onSelect(opt.value);
            };
    
            item.addEventListener('click', selectThis);
            // focus روی TV ممکن است با اسکرول مدام onSelect بزند؛ اگر وب موبایل است بهتر فقط click
            // item.addEventListener('focus', selectThis);
    
            container.appendChild(item);
        });
    
        return container;
    }

    // در صفحه‌ی hamrahang.ir/pair/{code} (سمت وب‌سایت اصلی)
    function ConfirmPairing(code) {
    
        if (!SN.user || !SN.user.loggedIn) {
            ShowLoginForm(function () {
                ConfirmPairing(code);   // بعد از لاگین موفق، دوباره امتحان کن
            });
            return;
        }
    
        gjl({ a: 'confirm_device', code: code }, function (success, j) {
    
            if (success && j.ok) {
                ShowMessage("دستگاه با موفقیت متصل شد ✅");
            } else {
                ShowMessage("کد نامعتبر یا منقضی شده است");
            }
        });
    }

    function InitPairPage() {
    
        var pathParts = window.location.pathname.split('/');
        // فرض: مسیر به فرم /pair/{code} یا فقط /pair هست
        var codeFromUrl = (pathParts[2] && /^\d{6}$/.test(pathParts[2])) ? pathParts[2] : null;
    
        var statusEl = document.getElementById('pair-status');
        var inputSection = document.getElementById('pair-code-input-section');
    
        if (codeFromUrl) {
    
            // کد از QR اومده → مستقیم برو سراغ تأیید (بعد از چک لاگین)
            HandlePairing(codeFromUrl, statusEl);
    
        } else {
    
            // کد در URL نبود → فرم ورود دستی نشون بده
            inputSection.style.display = 'block';
    
            document.getElementById('pair-submit-btn').addEventListener('click', function () {
    
                var manualCode = document.getElementById('pair-code-input').value.trim();
    
                if (!/^\d{6}$/.test(manualCode)) {
                    statusEl.innerHTML = "کد باید ۶ رقم باشد";
                    return;
                }
    
                HandlePairing(manualCode, statusEl);
            });
        }
    }
    
    function HandlePairing(code, statusEl) {
    
        if (!SN.user || !SN.user.loggedIn) {
    
            statusEl.innerHTML = "لطفاً ابتدا وارد حساب کاربری خود شوید";
            ShowLoginForm(function () {
                ConfirmPairing(code, statusEl);
            });
            return;
        }
    
        ConfirmPairing(code, statusEl);
    }
    
    function ConfirmPairing(code, statusEl) {
    
        gjl({ a: 'confirm_device', code: code }, function (success, j) {
    
            if (success && j && j.ok) {
                statusEl.innerHTML = "دستگاه با موفقیت متصل شد ✅";
            } else {
                statusEl.innerHTML = "کد نامعتبر یا منقضی شده است";
            }
        });
    }
    
    if (window.addEventListener){
        window.addEventListener("popstate", function(){ chp(); });
    }
    
    function GetSetting(key) {
        return SN.settings ? SN.settings[key] : undefined;
    }
    
    function SetSetting(key, value) {
        if (!SN.settings) SN.settings = {};
        SN.settings[key] = value;
        SaveSettingsToStorage();
    }
    
    function SaveSettingsToStorage() {
        try {
            localStorage.setItem('ha_settings', JSON.stringify(SN.settings));
        } catch (e) {
            console.log('SaveSettingsToStorage failed:', e.message);
        }
    }
    
    function LoadSettingsFromStorage() {
    
        var defaults = {
            audioQuality: 'high',
            videoQuality: 'high',
            defaultView: true,
            rotatingHeader: false,
            showImages: false,
            keepControllerVisible: false,
            lyricFontSize: 'md',
            lyricFontSizePx: 16,
            lyricFontFamily: '',
            lyricDisplayMode: 'normal',
            sleepTimer: 0,
            repeatMode: 'off',
            shuffleMode: false
        };
    
        try {
            var saved = localStorage.getItem('ha_settings');
            if (saved) {
                var parsed = JSON.parse(saved);
                for (var key in defaults) {
                    if (parsed[key] === undefined) {
                        parsed[key] = defaults[key];
                    }
                }
                SN.settings = parsed;
                return;
            }
        } catch (e) {
            console.log("LoadSettingsFromStorage failed:", e.message);
        }
    
        SN.settings = defaults;
    }
    
    const Arr_sm = [
        ['video', 'top'],
        ['video', 'latest'],
        ['song', 'top'],
        ['song', 'latest'],
        ['artist', 'top'],
        ['playlist', 'top']
    ];

    async function SPA() {

        const search = window.location.search;
        FSRC();
        
        const qParam = getQueryParam(search, 'tgWebAppStartParam');
        let startParam = [];
    
        if (qParam) {
            startParam = qParam.split('==');
        }

        for (const i of Arr_sm) {
            await gjs(i);
        }
        LoadProfile();
        LoadSettingsFromStorage();
        ensurePlayer();
        
        CheckLoginStatus(async () => {
            await HydrateProfileItems();
            
            if (startParam[0]) {
                chp(HS + startParam[0]);
            } else {
                chp();
            }
        });

    }

    async function HydrateProfileItems() {
        LoadProfile();
        if (!SN.profile) return;
    
        const fav = SN.profile.favorite || {};
    
        const songIds = [
            ...(fav.song || []),
            ...(fav.podcast || []),
            ...(Array.isArray(SN.profile.history) ? SN.profile.history : [])
        ];
        const uniqueSongs = [...new Set(songIds)];
        if (uniqueSongs.length) {
            await EnsureItems('song', uniqueSongs);
        }
    
        if (fav.album?.length) {
            await EnsureItems('album', fav.album);
        }
        
        if (fav.artist?.length) {
            await EnsureItems('artist', fav.artist);
        }

    }
    
    function getQueryParam(search, name) {
    
        if (!search) {
            return null;
        }
    
        search = search.substring(1);
    
        var params = search.split('&');
    
        for (var i = 0; i < params.length; i++) {
    
            var pair = params[i].split('=');
    
            if (decodeURIComponent(pair[0]) === name) {
    
                return pair.length > 1
                    ? decodeURIComponent(pair.slice(1).join('='))
                    : '';
            }
        }
    
        return null;
    }
    
    SPA();
    
    function CRNF(){
        const dtx = OA(document.createElement("div"), {className: "error-message-container"});
        const tx1 = OA(document.createElement("div"), {className: "text1 m-top-1-5", innerHTML: "Page not found!"});
        const tx2 = OA(document.createElement("div"), {className: "text2", innerHTML: "The requested page was not found"});
        const btn = OA(document.createElement("button"), {className: "btn btn-sm btn-model-4 m-top-1-5", type:"button", innerHTML: "Back to home"});
        dtx.append(tx1,tx2,btn);
        MCNT.append(dtx);
        btn.addEventListener("click", function(){ chp(HP); });
    }
    
    async function CRHP(P, QS) {
    
        const tabsConfig = [
            { key: 'top',      label: 'Featured', qs: 'top' },
            { key: 'latest',   label: 'Latest',   qs: 'latest' },
            { key: 'trending', label: 'Trending', qs: 'trending' },
            { key: 'popular',  label: 'Popular',  qs: 'popular' }
        ];
    
        const routeMap = {
            // ---- داده‌های عمومی ----
            'video_top':      { key: 'top_video',      label: null,                href: '/video/top',      fetch: ['video', 'top'],      type: 'musicvideo' },
            'song_top':       { key: 'top_song',       label: 'Top Songs',         href: '/song/top',       fetch: ['song', 'top'],       type: 'song' },
            'song_latest':    { key: 'latest_song',    label: 'New Tracks',        href: '/song/latest',    fetch: ['song', 'latest'],    type: 'song' },
            'video_latest':   { key: 'latest_video',   label: 'New Music Videos',  href: '/video/latest',   fetch: ['video', 'latest'],   type: 'musicvideo' },
            'artist_top':     { key: 'top_artist',     label: 'Top Artist',        href: '/artist/top',     fetch: ['artist', 'top'],     type: 'artist' },
            'playlist_top':   { key: 'top_playlist',   label: 'Top Playlist',      href: '/playlist/top',   fetch: ['playlist', 'top'],   type: 'playlist' },
    
            // ---- بخش‌های کاربر ----
            'user_history_song': {    key: 'history', label: 'Recently Played', href: '/library/history/song',    type: 'user', source: 'history', itemType: 'song', listMode: 'carousel'},
            'user_history_podcast': {    key: 'history', label: 'Recently Played Podcasts', href: '/library/history/podcast',    type: 'user', source: 'history', itemType: 'podcast', listMode: 'carousel'},
            'user_history_musicvideo': {    key: 'history', label: 'Recently Played Music Videos', href: '/library/history/musicvideo',    type: 'user', source: 'history', itemType: 'musicvideo', listMode: 'carousel'},
            'user_history_video': {    key: 'history', label: 'Recently Played Videos', href: '/library/history/video',    type: 'user', source: 'history', itemType: 'video', listMode: 'carousel'},
            'user_fav_song':        { key: 'favorite.song',  label: 'Your Favorite',     href: null,   type: 'user', source: 'favorite.song', itemType: 'song' },
            'user_fav_artist':      { key: 'favorite.artist',label: 'Your Followed',     href: null,   type: 'user', source: 'favorite.artist', itemType: 'artist' },
            'user_fav_album':       { key: 'favorite.album', label: 'Your Bookmark',     href: null,   type: 'user', source: 'favorite.album', itemType: 'album' },
            'user_fav_musicvideo':  { key: 'favorite.musicvideo', label: 'Your Favorite',     href: null,   type: 'user', source: 'favorite.musicvideo', itemType: 'musicvideo' }
        };

        // ترتیب کامل صفحه هوم (همه‌چیز از اینجا مدیریت می‌شود)
        const homeOrder = [
            'video_top',
            'song_top',
            'song_latest',
            'video_latest',
            'user_history_song',
            'user_history_podcast',
            'user_history_musicvideo',
            'user_history_video',
            'playlist_top',
            'artist_top',
            
        ];

        // ---- صفحه اصلی ----
        if (!P) {
            await renderHome();
            return;
        }
    
        // ---- مسیرهای جزئیات ----
        if (P === 'video' && !routeMap[`video_${QS}`]) {
            await CRVP(QS);
            return;
        }
        if (P === 'song' && !routeMap[`song_${QS}`]) {
            await CRSP(QS,'song');
            return;
        }
        if (P === 'album') {
            await CRSP(QS,'album');
            return;
        }
        if (P === 'playlist') {
            await CRPP(QS,'playlist');
            return;
        }
        if (P === 'artist' && !routeMap[`artist_${QS}`]) {
            await CRAP(QS);
            return;
        }
    
        // ---- مسیرهای لیست ----
        const routeKey = `${P}_${QS}`;
        const route = routeMap[routeKey];
    
        if (!route) {
            console.log('CRHP: Unknown route:', P, QS);
            await renderHome();
            return;
        }
    
        if (SN[route.key]?.length) {
            renderList(route);
            return;
        }
    
        await gjs(route.fetch);
        renderList(route);
    
    
        // ========================================
        function renderList(route) {
            const ar = SN[route.key];
            if (!ar || !ar.length) return;
    
            // تب‌ها
            const tabContainer = OA(document.createElement('div'), { className: 'main-tabs m-top-1' });
            const currentType = route.type === 'musicvideo' ? 'video' : route.type;
    
            tabsConfig.forEach(tab => {
                const href = `/${currentType}/${tab.qs}`;
                const isActive = (QS === tab.qs);
    
                const el = OA(document.createElement('a'), {
                    className: 'item' + (isActive ? ' active' : ''),
                    href: href,
                    innerHTML: tab.label
                });
                tabContainer.append(el);
            });
    
            MCNT.append(tabContainer);
    
            // محتوا
            if (route.type === 'video' || route.type === 'musicvideo') {
                CRGC(ar, route.type);
            }
            if (route.type === 'song' || route.type === 'podcast' || route.type === 'album' || route.type === 'playlist') {
                CRGL(ar, route.type);
            }
            if (route.type === 'artist') {
                CRGC(ar, route.type);
            }

            ADE();
        }
    
    
        // ========================================
        async function renderHome() {
            LoadProfile();
        
            for (const key of homeOrder) {
                const route = routeMap[key];
                if (!route) continue;
        
                // ---- بخش‌های کاربر ----
                if (route.type === 'user') {
                    const data = getUserData(route.source, route.itemType);
                    if (!data || !data.length) continue;
                
                    let readyIds = data;
                    if (route.itemType && ITEM_TYPES.indexOf(route.itemType) !== -1) {
                        readyIds = await EnsureItems(route.itemType, data);
                    }
                    if (!readyIds.length) continue;
                
                    if (route.label) {
                        const hed = OA(document.createElement('div'), { className: 'section-header m-top-3' });
                        const ht = OA(document.createElement('h2'), {
                            className: 'title',
                            innerHTML: route.label
                        });
                        if (route.href) {
                            const ha = OA(document.createElement('a'), {
                                className: 'see-all',
                                innerHTML: 'See All',
                                href: route.href
                            });
                            hed.append(ht, ha);
                        } else {
                            hed.append(ht);
                        }
                        MCNT.append(hed);
                    }
                
                    if (route.itemType === 'musicvideo' || route.itemType === 'video') {
                        CRSC(readyIds, route.itemType);
                    } else {
                        CRSC(readyIds, route.itemType);
                    }
                    continue;
                }
        
                // ---- بخش‌های عادی ----
                const ar = SN[route.key];
                if (!ar || !ar.length) continue;
        
                if (route.label) {
                    const hed = OA(document.createElement('div'), { className: 'section-header m-top-3' });
                    const ht  = OA(document.createElement('h2'), { className: 'title', innerHTML: route.label });
        
                    if (route.href) {
                        const ha = OA(document.createElement('a'), {
                            className: 'see-all',
                            innerHTML: 'See All',
                            href: route.href
                        });
                        hed.append(ht, ha);
                    } else {
                        hed.append(ht);
                    }
                    MCNT.append(hed);
                }
        
                if (route.type === 'musicvideo') CRSC(ar, 'musicvideo');
                if (route.type === 'song' || route.type === 'podcast' || route.type === 'album' || route.type === 'playlist') {
                    CRSC(ar, route.type);
                }
                if (route.type === 'artist') CRSC(ar, 'artist');
            }
        console.log(SN);
            ADE();
        }
    
    
        // تابع کمکی برای خواندن داده کاربر از روی مسیر نقطه‌ای
        function getUserData(path, itemType) {
            if (!SN.profile) return null;
        
            if (path === 'history') {
                const list = SN.profile.history || [];
                if (!itemType) return list;
                return list
                    .filter((row) => row && row.type === itemType)
                    .map((row) => row.id);
            }
            if (path === 'favorite.song') return SN.profile.favorite?.song;
            if (path === 'favorite.artist') return SN.profile.favorite?.artist;
            if (path === 'favorite.album') return SN.profile.favorite?.album;
            if (path === 'favorite.musicvideo') return SN.profile.favorite?.musicvideo;
            return null;
        }
    }
    
    async function CRLP(P, QS) {

        const routeMap = {
            history_song: {
                label: 'Recently Played',
                source: 'history',
                itemType: 'song',
                listMode: 'line',
                href: '/library/history/song'
            },
            history_podcast: {
                label: 'Recently Played Podcasts',
                source: 'history',
                itemType: 'podcast',
                listMode: 'line',
                href: '/library/history/podcast'
            },
            history_musicvideo: {
                label: 'Recently Played Music Videos',
                source: 'history',
                itemType: 'musicvideo',
                listMode: 'grid',
                href: '/library/history/musicvideo'
            },
            history_video: {
                label: 'Recently Played Videos',
                source: 'history',
                itemType: 'video',
                listMode: 'grid',
                href: '/library/history/video'
            },
    
            playlist: {
                label: 'Your Playlists',
                source: 'playlist',
                itemType: 'playlist',
                listMode: 'line',
                href: '/library/playlist'
            },
            favorite_song: {
                label: 'Your Favorite Songs',
                source: 'favorite.song',
                itemType: 'song',
                listMode: 'line',
                href: '/library/favorite/song'
            },
            favorite_artist: {
                label: 'Your Followed',
                source: 'favorite.artist',
                itemType: 'artist',
                listMode: 'carousel',
                href: '/library/favorite/artist'
            },
            favorite_album: {
                label: 'Your Bookmark Albums',
                source: 'favorite.album',
                itemType: 'album',
                listMode: 'line',
                href: '/library/favorite/album'
            },
            favorite_video: {
                label: 'Your Bookmark Videos',
                source: 'favorite.video',
                itemType: 'video',
                listMode: 'grid',
                href: '/library/favorite/video'
            },
            favorite_playlist: {
                label: 'Your Bookmark Playlists',
                source: 'favorite.playlist',
                itemType: 'playlist',
                listMode: 'carousel',
                href: '/library/favorite/playlist'
            },
            favorite_podcast: {
                label: 'Your Bookmark Podcasts',
                source: 'favorite.podcast',
                itemType: 'podcast',
                listMode: 'line',
                href: '/library/favorite/podcast'
            },
            favorite_musicvideo: {
                label: 'Your Favorite Music Videos',
                source: 'favorite.musicvideo',
                itemType: 'musicvideo',
                listMode: 'grid',
                href: '/library/favorite/musicvideo'
            }
        };
    
        // خلاصه library: اول بقیه، آخر چهار تاریخچه (مثل هوم)
        const libraryOrder = [
            'playlist',
            'favorite_song',
            'favorite_musicvideo',
            'favorite_artist',
            'favorite_album',
            'favorite_playlist',
            'history_song',
            'history_podcast',
            'history_musicvideo',
            'history_video'
        ];
    
        // /library
        if (!P) {
            await renderLibrary();
            return;
        }
    
        // /library/history/song → P=history, QS=song
        // /library/favorite/song → P=favorite, QS=song
        // /library/playlist → P=playlist
        let routeKey = P;
        if (P === 'history' && QS) {
            routeKey = 'history_' + QS;
        } else if (P === 'favorite' && QS) {
            routeKey = 'favorite_' + QS;
        }
    
        const route = routeMap[routeKey];
        if (!route) {
            console.log('CRLP: Unknown route:', P, QS);
            await renderLibrary();
            return;
        }
    
        await renderLibrarySection(route);
        ADE();
    
        async function renderLibrarySection(route) {
            LoadProfile();
    
            const data = getUserData(route.source, route.itemType);
            if (!data || !data.length) {
                MCNT.append(
                    OA(document.createElement('div'), {
                        className: 'text2 m-top-1',
                        innerHTML: 'موردی نیست'
                    })
                );
                return;
            }
    
            let readyIds = data;
            if (ITEM_TYPES.indexOf(route.itemType) !== -1) {
                readyIds = await EnsureItems(route.itemType, data);
            }
            if (!readyIds.length) return;
    
            const hed = OA(document.createElement('div'), { className: 'section-header m-top-1' });
            hed.append(
                OA(document.createElement('h1'), {
                    className: 'title',
                    innerHTML: route.label
                })
            );
            MCNT.append(hed);
    
            if (route.listMode === 'line') {
                CRGL(readyIds, route.itemType);
            } else if (route.listMode === 'grid') {
                CRGC(readyIds, route.itemType);
            } else {
                CRSC(readyIds, route.itemType);
            }
        }
    
        async function renderLibrary() {
            LoadProfile();
    
            for (const key of libraryOrder) {
                const route = routeMap[key];
                if (!route) continue;
    
                const data = getUserData(route.source, route.itemType);
                if (!data || !data.length) continue;
    
                let readyIds = data;
                if (ITEM_TYPES.indexOf(route.itemType) !== -1) {
                    readyIds = await EnsureItems(route.itemType, data);
                }
                if (!readyIds.length) continue;
    
                const hed = OA(document.createElement('div'), { className: 'section-header m-top-3' });
                const ht = OA(document.createElement('h2'), {
                    className: 'title',
                    innerHTML: route.label
                });
                const ha = OA(document.createElement('a'), {
                    className: 'see-all',
                    innerHTML: 'See All',
                    href: route.href
                });
                hed.append(ht, ha);
                MCNT.append(hed);
    
                CRSC(readyIds, route.itemType);
            }
    
            ADE();
        }
        
        function getUserData(path, itemType) {
            if (!SN.profile) return null;
        
            if (path === 'history') {
                const list = Array.isArray(SN.profile.history) ? SN.profile.history : [];
                return list
                    .filter((row) => {
                        if (!row || row.id == null) return false;
                        if (!itemType) return true;
                        return String(row.type) === String(itemType);
                    })
                    .map((row) => row.id);
            }
            if (path === 'playlist') {
                return SN.profile.playlist || [];
            }
            if (path.indexOf('favorite.') === 0) {
                const t = path.slice(9);
                return (SN.profile.favorite && SN.profile.favorite[t]) || [];
            }
            return null;
        }
    }

    async function CRAP(q) {
        
        const e = await UART(['artist', q]);
        if (!e) return;

        var au = e.url;


        var dhc = OA(document.createElement("div"), {className: "page-detail-header-container"});
        var dtn = OA(document.createElement("div"), {className: "thumbail rounded-circle"});
        var spn = OA(document.createElement("span"), {
            className: "placeholder rounded-circle",
            innerHTML: svg_sng
        });

        dtn.append(spn);

        const thumb = e.meta && e.meta?.tmb || null;
        if (thumb) dtn.append(OA(document.createElement('img'), {className: "rounded-circle", src: UT+thumb+".jpg" }));

        var dct = OA(document.createElement("div"), {className: "content"});
        var tlt = OA(document.createElement("div"), {className: "title"});
        var th1 = OA(document.createElement("h1"), {
            title: e.name_fa,
            innerHTML: e.name
        });

        tlt.append(th1);


        const dtl = OA(document.createElement("div"), {className: "detailes"});
        let dtlv = '';
        if (e.meta.flw) {
            dtlv += e.meta.flw + ' Followers'
            
        }
        dtl.innerHTML = dtlv;

        dct.append(tlt, dtl);
        dhc.append(dtn, dct);
        MCNT.append(dhc);


        const dac = OA(document.createElement("div"), {
            className: "page-detail-actions"
        });

        const dpl = OA(document.createElement("div"), {
            className: "play",
            innerHTML: svg_rdo
        });

        dpl.addEventListener('click', () => {
            if (!e.songs || !e.songs.length) return;
            setPlayQueue(e.songs, e.songs[0], 'song');
        });

        const isFav = IsFavorite('artist', e.id);
        const bfl = OA(document.createElement('div'), {
            className: 'btn btn-sm ' + (isFav ? 'btn-model-4' : 'btn-model-3'),
            innerHTML: isFav ? 'Following' : 'Follow'
        });
        
        bfl.addEventListener('click', () => {
            const nowFavorite = ToggleFavorite('artist', e.id);
        
            // متن
            bfl.innerHTML = nowFavorite ? 'Following' : 'Follow';
        
            // کلاس‌ها
            bfl.classList.remove('btn-model-3', 'btn-model-4');
            bfl.classList.add(nowFavorite ? 'btn-model-4' : 'btn-model-3');
        });
            
        var dbs = OA(document.createElement("div"), {
            className: "btn btn-model-1",
            innerHTML: svg_sha
        });
        
        dbs.addEventListener('click', () => {
            copyText(
                    window.location.href,
                    'Artist link copied to clipboard'
                );
        });
        
        const mor = OA(document.createElement('div'), { className: 'btn btn-model-1', innerHTML: svg_dot });
        mor._item = e;
        mor.addEventListener("click", ()=>{
                 CRBW(e.id,'artist');
            },false);

        dac.append(dpl, bfl, dbs, mor);
        MCNT.append(dac);


        if (e.songs) {

            var shd = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">Latest Songs</h2>'
            });

            MCNT.append(shd);

            CRSC(e.songs, 'song');
        }

        if (e.musicvideos) {

            var shd = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">Latest Music videos</h2>'
            });

            MCNT.append(shd);

            CRSC(e.musicvideos, 'musicvideo');
        }   

        if (e.songs) {

            var shd2 = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">Songs</h2>'
            });

            MCNT.append(shd2);

            CRGL(e.songs, 'song');
        }


        if (e.albums) {

            var shd3 = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">Albums</h2>'
            });

            MCNT.append(shd3);

            CRSC(e.albums, 'album');
        }

        if (e.meta && (e.meta.bd || e.meta.tlg || e.meta.ins || e.meta.cc || e.meta.nat )) {
            const sabt = OA(document.createElement("div"), {className: "section-header m-top-3",
                innerHTML: '<h2 class="title">About Artist</h2>'
            });
            const abt = CRAB(e.meta);
            MCNT.appendChild(sabt);
            MCNT.appendChild(abt);
        }
        
        ADE();
    
    }
    
    async function CRSP(q,T) {
        
        let e = null;
        let url = HS;
            
        if (T == 'song') {e = await USNG(['song', q]); url = HS; }
        if (T == 'album') {e = await ULBM(['album', q]); url = HL; }
        if (T == 'artist') {e = await UART(['artist', q]); url = HA; }
        if (T == 'podcast') {e = await USNG(['podcast', q]); url = HS; }

        if (!e) return;
    
        //AudLst[0] = e;
    
        // ---- هدر ----
        const dhc = OA(document.createElement('div'), { className: 'page-detail-header-container' });
        const dtn = OA(document.createElement('div'), { className: 'thumbail' });
        const spn = OA(document.createElement('span'), { className: 'placeholder', innerHTML: svg_sng });
        dtn.append(spn);
    
        const thumb = e.meta?.tmb || null;
        if (thumb) dtn.append(OA(document.createElement('img'), { src: `${UT}${thumb}.jpg` }));
    
        const dct = OA(document.createElement('div'), { className: 'content' });
        const tlt = OA(document.createElement('div'), { className: 'title' });
        const th1 = OA(document.createElement('h1'), {
            title: e.name_fa || '',
            innerHTML: e.name || ''
        });
        tlt.append(th1);
        
        // هنرمند
        const art = OA(document.createElement('div'), { className: 'artists', innerHTML: 'By ' });
        // بهتر است از لیست artists استفاده کنی نه e.artist
        if (e.artists && e.artists.length > 0) {
            const firstArtistId = typeof e.artists[0] === 'object' ? e.artists[0].id : e.artists[0];
            const artist = SN.artist?.[firstArtistId];
            if (artist) {
                const ali = OA(document.createElement('a'), {
                    href: `${HA}${artist.id}/${artist.url || ''}`,
                    innerHTML: artist.name || ''
                });
                art.append(ali);
            }
        }
    
        const dtl = OA(document.createElement('div'), { className: 'detailes' });
        const artl = CRAL(e.artists);
        dtl.appendChild(artl);
        
        dct.append(tlt, art, dtl);
        
        const dlc = OA(document.createElement('div'), { className: 'lyric' });
        dlc.setAttribute('data-ha-lrc', '1');
        
        dhc.append(dtn, dct, dlc );
        MCNT.append(dhc);
        // لیریک صفحه جزئیات (خروجی موتور مشترک)
        try {
            if (LyricsEngine.hasLyrics(e)) {
                LyricsEngine.loadFromItem(e);
                // صفحه سانگ: فقط نرمال — ستینگ پلیر اعمال نشود
                LyricsEngine.mount(
                    dlc,
                    LyricsEngine.synced ? 'sync' : 'static',
                    LyricsEngine.synced ? 'normal' : 'static'
                );
            }
        } catch (err) {}

        // ---- دکمه‌های اکشن ----
        const dac = OA(document.createElement('div'), { className: 'page-detail-actions' });
        const dbp = OA(document.createElement('div'));
        
        if (T === 'album') {
            dbp.className = 'play';
            dbp.innerHTML = svg_rdo;
            dbp.addEventListener('click', (ev) => {
                ev.preventDefault();
                ev.stopPropagation();
                const ids = Array.isArray(e.songs) ? e.songs : [];
                if (!ids.length) return;
                setPlayQueue(ids, ids[0], 'song');
            });
        } else {
            dbp.className = 'play-pause';
            dbp.innerHTML = svg_plys;
            dbp.addEventListener('click', (ev) => {
                ev.preventDefault();
                ev.stopPropagation();
        
                const type =
                    T === 'podcast' || (e.meta && e.meta.type === 'podcast')
                        ? 'podcast'
                        : 'song';
        
                const P = ensurePlayer();
                const np = P.nowPlaying;
                const same =
                    np &&
                    Number(np.id) === Number(e.id) &&
                    np.player === 'audio';
        
                if (same) {
                    TogglePlayPause();
                    return;
                }
        
                setPlayQueue([{ id: e.id, type }], e.id, type);
            });
        }
    
        const isFav = IsFavorite(T, e.id);
        const dbf = OA(document.createElement('div'), {
            className: 'btn btn-model-1 focusable',
            innerHTML: isFav ? svg_fav2 : svg_fav
        });
    
        dbf.addEventListener('click', () => {
            const nowFavorite = ToggleFavorite(T, e.id);
            dbf.innerHTML = nowFavorite ? svg_fav2 : svg_fav;
        });
    
        
        const dbd = OA(document.createElement('div'), { className: 'btn btn-model-1', innerHTML: svg_dons });
        dbd.addEventListener('click', () => {
            CRBW(e.id, T, 'don', { fresh: true });
        });

        var dbs = OA(document.createElement("div"), {
            className: "btn btn-model-1",
            innerHTML: svg_sha
        });
        
        dbs.addEventListener('click', () => {
            copyText(
                    window.location.href,
                    'Artist link copied to clipboard'
                );
        });
        
        const mor = OA(document.createElement('div'), { className: 'btn btn-model-1', innerHTML: svg_dot });
        mor._item = e;
        mor.addEventListener("click", ()=>{
                 CRBW(e.id,T);
            },false);
    
        // دانلود برای song / podcast
        const showDon = (T === 'song' || T === 'podcast');
        dac.append(dbp, dbf, showDon ? dbd : '', dbs, mor);
        MCNT.append(dac);
        
        if (e.songs) {

            const shd2 = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">Songs</h2>'
            });

            MCNT.append(shd2);

            CRGL(e.songs, 'song');
        }

        // song2video → ویدیو / موزیک‌ویدیو مرتبط (کروسل افقی)
        const relMv = Array.isArray(e.musicvideos) ? e.musicvideos : [];
        const relV = Array.isArray(e.videos) ? e.videos : [];
        if (relMv.length || relV.length) {
            const shdV = OA(document.createElement('div'), {
                className: 'section-header m-top-3',
                innerHTML: '<h2 class="title">Videos</h2>'
            });
            MCNT.append(shdV);
            if (relMv.length) CRSC(relMv, 'musicvideo');
            if (relV.length) CRSC(relV, 'video');
        }
    
        if (e.artists){
            const dal = OA(document.createElement('div'), { className: 'page-artists-list' });
            const shd2 = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">Artists</h2>'
            });
            const artist = CRAL(e.artists);
            dal.append(shd2,artist);
            MCNT.appendChild(dal);
        }

        if (e.meta && (e.meta.des || e.meta.rls || e.meta.ncp != null || e.meta.dur != null)) {
            const sabt = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">About ' + T + '</h2>'
            });
            const abt = CRAB(e.meta);
            MCNT.appendChild(sabt);
            MCNT.appendChild(abt);
        }

        ADE();
    }
    
    function CRAB(m) {

        const abt = OA(document.createElement("div"), {className: "about-container"});
    
        if (m.rls) {
            var sfn = OA(document.createElement("span"), {
                className: "text1 m-top-1",
                textContent: "Release date"
            });

            var sp1 = OA(document.createElement("span"), {
                className: "text2",
                textContent: new Date(m.rls).toLocaleString("en-us", {
                day: "2-digit",
                month: "short",
                year: "numeric"
            })});
            abt.append(sfn, sp1);
        }
        
        if (m.bd) {
            var sfn = OA(document.createElement("span"), {
                className: "text1 m-top-1",
                textContent: "Born"
            });

            var sp1 = OA(document.createElement("span"), {
                className: "text2",
                textContent: new Date(m.bd).toLocaleString("en-us", {
                day: "2-digit",
                month: "short",
                year: "numeric"
            })});

            abt.append(sfn, sp1);
        }

        if (m.ins) {

            var sfn = OA(document.createElement("span"), {
                className: "text1 m-top-1",
                innerHTML: "Instagram"
            });

            var sp1 = OA(document.createElement("span"), {
                className: "text2",
                innerHTML: "@" + m.ins
            });

            abt.append(sfn, sp1);
        }
    
    
        if (m.tlg) {

            var sfn = OA(document.createElement("span"), {
                className: "text1 m-top-1",
                innerHTML: "Telegram"
            });

            var sp1 = OA(document.createElement("span"), {
                className: "text2",
                innerHTML: "@" + m.tlg
            });

            abt.append(sfn, sp1);
        }
    
        if (m.ncp != null) {
            var sfn = OA(document.createElement("span"), {
                className: "text1 m-top-1",
                textContent: "Play count"
            });

            var sp1 = OA(document.createElement("span"), {
                className: "text2",
                textContent: m.ncp
            });
            abt.append(sfn, sp1);
        }
    
        if (m.dur != null) {
            var dur = parseInt(m.dur, 10);
            var dmin = Math.floor(dur / 60);
            var dsec = dur % 60;
    
            var sfn = OA(document.createElement("span"), {
                className: "text1 m-top-1",
                textContent: "Duration"
            });

            var sp1 = OA(document.createElement("span"), {
                className: "text2",
                textContent: dmin + ":" + (dsec < 10 ? "0" : "") + dsec
            });
            abt.append(sfn, sp1);
        }
    
        if (m.des) {
            var sfn = OA(document.createElement("span"), {
                className: "text1 m-top-1",
                textContent: "Description"
            });

            var sp1 = OA(document.createElement("div"), {
                className: "text2",
                textContent: m.des
            });
    
            sp1.style.whiteSpace = "pre-wrap";
            abt.append(sfn, sp1);
        }
        
        return abt;
    }
    
    async function resolveVideoItem(q) {
        // فقط فول‌کارت کافی است — مینی‌کارت از song2video فاقد songs است
        if (SN.musicvideo && SN.musicvideo[q] && SN.musicvideo[q].full) {
            return SN.musicvideo[q];
        }
        if (SN.video && SN.video[q] && SN.video[q].full) {
            return SN.video[q];
        }
    
        try {
            await gjs(['video', q]);
        } catch (err) {
            console.warn('resolveVideoItem failed', q, err);
            return null;
        }
    
        if (SN.musicvideo && SN.musicvideo[q]) return SN.musicvideo[q];
        if (SN.video && SN.video[q]) return SN.video[q];
        return null;
    }
    
    function enterInlineVideoStage(stage, thumbWrap, videoSlot, inlineBar) {
        if (!Video || !videoSlot) return;
    
        const P = ensurePlayer();
        P.inlineVideo = {
            stage: stage,
            thumbWrap: thumbWrap,
            videoSlot: videoSlot,
            inlineBar: inlineBar,
            itemId: P.nowPlaying ? P.nowPlaying.id : null
        };
    
        stage.classList.add('is-playing');
        if (thumbWrap) thumbWrap.classList.add('vis-none');
    
        videoSlot.appendChild(Video);
        Video.classList.remove('vis-none');
        Video.controls = false;
    
        if (inlineBar) inlineBar.classList.remove('vis-none');
    }
    
    function rehomeVideoToPlayer() {
        if (!Video || !Player) return;
        if (Video.parentNode !== Player) {
            Player.insertBefore(Video, Player.firstChild);
        }
    }
    
    function remountInlineVideoIfPossible() {
        const P = ensurePlayer();
        const iv = P.inlineVideo;
        if (!iv || !iv.videoSlot) {
            rehomeVideoToPlayer();
            return;
        }
        // stage هنوز در DOM است؟
        if (!document.body.contains(iv.videoSlot)) {
            P.inlineVideo = null;
            rehomeVideoToPlayer();
            return;
        }
        // همان ویدیوی در حال پخش
        if (P.nowPlaying && P.nowPlaying.player === 'video') {
            enterInlineVideoStage(iv.stage, iv.thumbWrap, iv.videoSlot, iv.inlineBar);
            return;
        }
        rehomeVideoToPlayer();
    }
    
    async function CRVP(q) {
        const e = await resolveVideoItem(q);
        if (!e) return;
    
        const actualType = (e.meta && e.meta.type === 'video') ? 'video' : 'musicvideo';
        const dhc = OA(document.createElement('div'), {className: 'page-detail-header-container video-page-header'});
        const stage = OA(document.createElement('div'), {className: 'video-stage ratio ratio-16x9'});
        const thumbWrap = OA(document.createElement('div'), {className: 'video-stage-thumb'});
        // ویدیو: tmv اولویت دارد، بعد tmb
        const thumb = (e.meta && (e.meta.tmv || e.meta.tmb)) ? (e.meta.tmv || e.meta.tmb) : null;
        if (thumb) {
            thumbWrap.append(
                OA(document.createElement('img'), {
                    className: 'video-poster',
                    src: UT + thumb + '.jpg'
                })
            );
        }

        const videoSlot = OA(document.createElement('div'), {className: 'video-stage-slot'});
        
        const inlineBar = OA(document.createElement('div'), {className: 'video-inline-controls vis-none'});
        
        const seek = OA(document.createElement('input'), {
            type: 'range',
            className: 'video-inline-seek',
            min: 0,
            max: 100,
            step: 0.1,
            value: 0
        });
        
        const row = OA(document.createElement('div'), {
            className: 'video-inline-row'
        });
        
        const times = OA(document.createElement('div'), {
            className: 'video-inline-times'
        });
        const tCur = OA(document.createElement('span'), {
            className: 't-cur',
            innerHTML: '0:00'
        });
        const tSep = OA(document.createElement('span'), { innerHTML: ' / ' });
        const tDur = OA(document.createElement('span'), {
            className: 't-dur',
            innerHTML: '0:00'
        });
        times.append(tCur, tSep, tDur);
        
        const actions = OA(document.createElement('div'), {
            className: 'video-inline-actions'
        });
        
        const stBtn = OA(document.createElement('div'), {
            className: 'video-inline-st',
            title: 'Settings',
            innerHTML: svg_fsc
        });
        stBtn.addEventListener('click', () => {
            // بعداً: باز کردن پنل کیفیت / سرعت
            // فعلاً خالی یا console
        });
        
        const fsBtn = OA(document.createElement('div'), {
            className: 'video-inline-fs',
            title: 'Fullscreen',
            innerHTML: svg_fsc
        });
        fsBtn.addEventListener('click', openPlayerShell);
        
        actions.append(stBtn, fsBtn);
        row.append(times, actions);
        inlineBar.append(seek, row);

        stage.append(thumbWrap, videoSlot, inlineBar);
        // تا قبل از play: inlineBar.classList.add('vis-none');

        dhc.append(stage);
    
        const dct = OA(document.createElement('div'), { className: 'content' });
        const tlt = OA(document.createElement('div'), { className: 'title' });
        tlt.append(
            OA(document.createElement('h1'), {
                title: e.name_fa || '',
                innerHTML: e.name || ''
            })
        );

        const art = OA(document.createElement('div'), { className: 'artists', innerHTML: 'By ' });
        if (e.artists && e.artists.length > 0) {
            const firstArtistId = typeof e.artists[0] === 'object' ? e.artists[0].id : e.artists[0];
            const artist = SN.artist?.[firstArtistId];
            if (artist) {
                const ali = OA(document.createElement('a'), {
                    href: `${HA}${artist.id}/${artist.url || ''}`,
                    innerHTML: artist.name || ''
                });
                art.append(ali);
            }
        }
    
        const dtl = OA(document.createElement('div'), { className: 'detailes' });
        const artl = CRAL(e.artists);
        dtl.appendChild(artl);
        
        dct.append(tlt, art, dtl);

        dhc.append(dct);
        MCNT.append(dhc);
    
        const dac = OA(document.createElement('div'), {
            className: 'page-detail-actions'
        });
        
        const dbp = OA(document.createElement('div'), {
            className: 'play-pause',
            innerHTML: svg_plys
        });
        
        dbp.addEventListener('click', (ev) => {
            ev.preventDefault();
            ev.stopPropagation();
            if (isNowPlayingId(e.id, 'video')) {
                TogglePlayPause();
                enterInlineVideoStage(stage, thumbWrap, videoSlot, inlineBar);
                return;
            }
            startThisVideo();
        });
    
        const isFav = IsFavorite(actualType, e.id);
        const dbf = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: isFav ? svg_fav2 : svg_fav
        });
        dbf.addEventListener('click', () => {
            const now = ToggleFavorite(actualType, e.id);
            dbf.innerHTML = now ? svg_fav2 : svg_fav;
        });
    
        const dbd = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: svg_dons
        });
        dbd.addEventListener('click', () => {
            CRBW(e.id, actualType, 'don', { fresh: true });
        });

        const dbs = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: svg_sha
        });
        dbs.addEventListener('click', () => {
            copyText(window.location.href, 'Link copied');
        });
    
        const mor = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: svg_dot
        });
        mor.addEventListener('click', () => {
            CRBW(e.id, actualType);
        });
    
        dac.append(dbp, dbf, dbd, dbs, mor);
        MCNT.append(dac);

        // song2video → آهنگ‌های مرتبط
        const relSongs = Array.isArray(e.songs) ? e.songs : [];
        if (relSongs.length) {
            const shdS = OA(document.createElement('div'), {
                className: 'section-header m-top-3',
                innerHTML: '<h2 class="title">Songs</h2>'
            });
            MCNT.append(shdS);
            CRGL(relSongs, 'song');
        }

        if (e.artists){
            const dal = OA(document.createElement('div'), { className: 'page-artists-list' });
            const shd2 = OA(document.createElement("div"), {
                className: "section-header m-top-3",
                innerHTML: '<h2 class="title">Artists</h2>'
            });
            const artist = CRAL(e.artists);
            dal.append(shd2,artist);
            MCNT.appendChild(dal);
        }
    
        function startThisVideo() {
            const P = ensurePlayer();
            const same =
                P.nowPlaying &&
                P.nowPlaying.player === 'video' &&
                Number(P.nowPlaying.id) === Number(e.id);
        
            if (same) {
                // از قبل همین ویدیو است → فقط pause/play
                TogglePlayPause();
                enterInlineVideoStage(stage, thumbWrap, videoSlot, inlineBar);
                return;
            }
        
            setPlayQueue([{ id: e.id, type: actualType }], e.id, actualType);
            enterInlineVideoStage(stage, thumbWrap, videoSlot, inlineBar);
        }
        
        const P = ensurePlayer();
        const sameVideo =
            P.nowPlaying &&
            P.nowPlaying.player === 'video' &&
            Number(P.nowPlaying.id) === Number(e.id);
        
        if (sameVideo) {
            enterInlineVideoStage(stage, thumbWrap, videoSlot, inlineBar);
        }
    
        ADE();
    }
    
    function beforePageClear() {
        const P = ensurePlayer();
        // ویدیو را از slot صفحه بکش بیرون تا با innerHTML از بین نرود
        rehomeVideoToPlayer();
        if (P.inlineVideo) P.inlineVideo = null;
    }

    function leaveInlineVideoStage() {
        const video = document.querySelector('.main-video');
        if (!video) return;
        // برگرداندن video به جای ثابت انتهای body (اگر لازم)
        // document.body.appendChild(video);
        // video.classList.add('vis-none');
    }
    
    function getVideoFsRoot() {
        // اگر داخل صفحه ویدیو هستیم و stage هست:
        const stage = document.querySelector('.video-stage.is-playing');
        if (stage) return stage;
        // وگرنه باکس ویدیو داخل max:
        const maxSlot = document.querySelector('.maximum-player-mobile .video-fs-root');
        if (maxSlot) return maxSlot;
        return document.querySelector('.main-video');
    }
    
    async function toggleVideoFullscreen() {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
            return;
        }
        const root = getVideoFsRoot();
        if (!root) return;
        if (root.requestFullscreen) await root.requestFullscreen();
        else if (root.webkitRequestFullscreen) root.webkitRequestFullscreen();
    }
    
    function openPlayerShell() {
        if (!Player || !MCLN) return;
        Player.classList.remove('vis-none');
        MCLN.classList.add('vis-none');
        // در صورت نیاز player-box را هم نشان بده
        const box = document.getElementById('player-box');
        if (box) box.classList.remove('vis-none');
        rehomeVideoToPlayer();
        
        const fsRoot = Player; // یا box اگر می‌خواهی کل player-box
        const req =
            fsRoot.requestFullscreen ||
            fsRoot.webkitRequestFullscreen ||
            fsRoot.msRequestFullscreen;
        if (req) {
            const p = req.call(fsRoot);
            if (p && p.catch) p.catch(() => {});
        }
    }
    
    function closePlayerShell() {
        if (!Player || !MCLN) return;

        // خروج از فول‌اسکرین مرورگر (اگر فعال است)
        const exit =
            document.exitFullscreen ||
            document.webkitExitFullscreen ||
            document.msExitFullscreen;
        if (exit && document.fullscreenElement) {
            exit.call(document).catch(() => {});
        }
    
        Player.classList.add('vis-none');
        MCLN.classList.remove('vis-none');
        const box = document.getElementById('player-box');
        if (box) box.classList.add('vis-none');
    
        remountInlineVideoIfPossible();
    }
    
    if (!document._haFsChange) {
        document._haFsChange = true;
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                // فقط اگر شل ما هنوز «باز» به نظر می‌رسد
                if (Player && !Player.classList.contains('vis-none')) {
                    Player.classList.add('vis-none');
                    if (MCLN) MCLN.classList.remove('vis-none');
                    const box = document.getElementById('player-box');
                    if (box) box.classList.add('vis-none');
                    remountInlineVideoIfPossible();
                }
            }
        });
    }

    const LOCAL_PLAYLIST_MAX = 99;
    
    function nextLocalPlaylistId() {
        LoadProfile();
        if (!SN.playlist || typeof SN.playlist !== 'object') SN.playlist = {};
    
        const used = new Set();
        // از profile
        (SN.profile.playlist || []).forEach((id) => {
            const n = Number(id);
            if (n >= 1 && n <= LOCAL_PLAYLIST_MAX) used.add(n);
        });
        // از SN
        Object.keys(SN.playlist).forEach((k) => {
            const n = Number(k);
            if (n >= 1 && n <= LOCAL_PLAYLIST_MAX) used.add(n);
        });
    
        for (let i = 1; i <= LOCAL_PLAYLIST_MAX; i++) {
            if (!used.has(i)) return i;
        }
        return null; // پر شده
    }
    
    function slugifyPlaylistName(name) {
        return String(name || 'playlist')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^\w\-]/g, '')
            .slice(0, 80) || 'playlist';
    }
    
    async function createPlaylist(name, items = []) {
        const trimmed = (name && String(name).trim()) || '';
        if (!trimmed) return null;
    
        // ---- لاگین: سرور ----
        if (SN.user?.loggedIn && SN.accessToken) {
            const j = await gja({
                a: 'create_playlist',
                name: trimmed,
                items: (items || []).map((row) => ({
                    id: row.id,
                    type: row.type || 'song'
                }))
            });
    
            if (!j || j.error) {
                if (j && j.error === 'playlist_limit') {
                    // روی سرور جا نیست؛ فقط دستگاه
                    return createLocalPlaylist(trimmed, items);
                }
                console.warn('create_playlist failed', j);
                return null;
            }
    
            const pl = await ingestPlaylistObject(j.playlist);
            SaveProfile();
            return pl;
        }
    
        // ---- مهمان: لوکال ----
        return createLocalPlaylist(trimmed, items);
    }
    
    /**
     * @param {string} name
     * @param {Array<{id, type, position?}>} [items]
     * @returns {object|null} پلی‌لیست ساخته‌شده
     */
    function createLocalPlaylist(name, items = []) {
        LoadProfile();
        if (!HAS_LOCAL) {
            console.warn('createLocalPlaylist: no localStorage');
            return null;
        }
    
        const id = nextLocalPlaylistId();
        if (id == null) {
            console.warn('createLocalPlaylist: max local playlists reached');
            return null;
        }
    
        if (!SN.playlist || typeof SN.playlist !== 'object') SN.playlist = {};
        if (!Array.isArray(SN.profile.playlist)) SN.profile.playlist = [];
    
        const timeline = (items || []).map((row, i) => ({
            id: row.id,
            type: row.type || 'song',
            position: row.position != null ? row.position : i
        }));
        timeline.sort((a, b) => a.position - b.position);
    
        let ncs = 0, ncp = 0, ncm = 0, ncv = 0;
        for (const row of timeline) {
            if (row.type === 'song') ncs++;
            else if (row.type === 'podcast') ncp++;
            else if (row.type === 'musicvideo') ncm++;
            else if (row.type === 'video') ncv++;
        }
    
        const pl = {
            id,
            name: (name && String(name).trim()) || ('Playlist ' + id),
            url: slugifyPlaylistName(name),
            local: true,
            full: true,
            public: false,
            meta: {
                nci: timeline.length,
                ...(ncs ? { ncs } : {}),
                ...(ncp ? { ncp } : {}),
                ...(ncm ? { ncm } : {}),
                ...(ncv ? { ncv } : {})
            },
            items: timeline
        };
    
        SN.playlist[id] = pl;
        if (!SN.profile.playlist.includes(id)) {
            SN.profile.playlist.push(id);
        }
        SaveProfile();
    
        return pl;
    }
    
        /** type: song | podcast | musicvideo | video */
    function addItemToPlaylist(playlistId, itemId, itemType) {
        LoadProfile();
        if (!SN.playlist) SN.playlist = {};
    
        const pl = SN.playlist[playlistId];
        if (!pl) return false;
    
        if (!Array.isArray(pl.items)) pl.items = [];
    
        // تکراری؟ سکوت کن
        const exists = pl.items.some(
            (row) => row.id === itemId && row.type === itemType
        );
        if (exists) return true;
    
        const position =
            pl.items.length > 0
                ? Math.max(...pl.items.map((r) => r.position || 0)) + 1
                : 0;
    
        pl.items.push({ id: itemId, type: itemType, position });
        pl.items.sort((a, b) => a.position - b.position);
    
        // شمارنده‌ها
        let ncs = 0, ncp = 0, ncm = 0, ncv = 0;
        for (const row of pl.items) {
            if (row.type === 'song') ncs++;
            else if (row.type === 'podcast') ncp++;
            else if (row.type === 'musicvideo') ncm++;
            else if (row.type === 'video') ncv++;
        }
        pl.meta = pl.meta || {};
        pl.meta.nci = pl.items.length;
        if (ncs) pl.meta.ncs = ncs; else delete pl.meta.ncs;
        if (ncp) pl.meta.ncp = ncp; else delete pl.meta.ncp;
        if (ncm) pl.meta.ncm = ncm; else delete pl.meta.ncm;
        if (ncv) pl.meta.ncv = ncv; else delete pl.meta.ncv;
    
        pl.full = true;
        SN.playlist[playlistId] = pl;
    
        // persist لوکال (و سروری که در profile است)
        if (!Array.isArray(SN.profile.playlist)) SN.profile.playlist = [];
        if (!SN.profile.playlist.includes(playlistId)) {
            SN.profile.playlist.push(playlistId);
        }
        // اگر playlistEntities داری اینجا هم آپدیت کن
        if (SN.profile.playlistEntities) {
            SN.profile.playlistEntities[playlistId] = pl;
        }
        SaveProfile();
        
        if (SN.user?.loggedIn && SN.accessToken && !pl.local) {
            const n = Number(playlistId);
            if (!(n >= 1 && n <= LOCAL_PLAYLIST_MAX)) {
                gja({
                    a: 'add_playlist_item',
                    playlist_id: playlistId,
                    id: itemId,
                    type: itemType
                }).then((j) => {
                    if (j && j.error === 'item_limit') {
                        pl.syncCapped = true;
                        persistPlaylist(pl);
                        // لوکال قبلاً اضافه شده — درست
                    }
                }).catch(() => {});
            }
        }
    
        return true;
    }
    
    /** داخل منوی Add to Playlist — نه CRGL */
    function renderPlaylistPicker(container, itemId, itemType, onPicked) {
        LoadProfile();
        const ids = SN.profile.playlist || [];
    
        if (!ids.length) {
            container.append(
                OA(document.createElement('div'), {
                    className: 'text2',
                    innerHTML: 'ابتدا یک پلی‌لیست بسازید'
                })
            );
            return;
        }
    
        const sec = OA(document.createElement('div'), {
            className: 'playlist-picker-list'
        });
    
        ids.forEach((pid) => {
            const e = SN.playlist && SN.playlist[pid];
            if (!e) return;
    
            const el = OA(document.createElement('div'), {
                className: 'track-line playlist-pick-row'
            });
    
            const art = OA(document.createElement('div'), {
                className: 'artwork ratio ratio-1x1'
            });
            art.append(OA(document.createElement('span'), { className: 'placeholder' }));
            const thumb = e.meta?.tmb;
            if (thumb) {
                art.append(OA(document.createElement('img'), { src: UT + thumb + '.jpg' }));
            }
    
            const dst = OA(document.createElement('div'), { className: 'texts' });
            const det = OA(document.createElement('div'), {
                className: 'title',
                innerHTML: e.name || ('Playlist ' + pid)
            });
            const sub = OA(document.createElement('div'), {
                className: 'text2',
                innerHTML: (e.meta?.nci || 0) + ' items'
            });
            dst.append(det, sub);
    
            el.append(art, dst);
    
            el.addEventListener('click', () => {
                addItemToPlaylist(pid, itemId, itemType);
                if (typeof onPicked === 'function') onPicked(pid);
            });
    
            sec.append(el);
        });
    
        container.append(sec);
    }
    
    function countCloudPlaylists() {
        LoadProfile();
        let n = 0;
        for (const id of SN.profile.playlist || []) {
            const pl = SN.playlist && SN.playlist[id];
            if (!pl) continue;
            const num = Number(pl.id);
            const isLocal = pl.local === true || (num >= 1 && num <= LOCAL_PLAYLIST_MAX);
            if (!isLocal) n++;
        }
        // یا از سرور: SN.playlistLimits + get؛ فعلاً شمارش غیرلوکال
        return n;
    }
    
    async function applyServerPlaylists(playlists) {
        if (!Array.isArray(playlists)) return;
        LoadProfile();
        for (const raw of playlists) {
            await ingestPlaylistObject(raw);
        }
        SaveProfile();
    }
    
    async function ingestPlaylistObject(raw) {
        if (!raw || raw.id == null) return null;
    
        const pl = await UPLS(['playlist', raw]);
        if (!pl) return null;
    
        delete pl.local;
    
        const id = pl.id;
        if (!Array.isArray(SN.profile.playlist)) SN.profile.playlist = [];
        if (!SN.profile.playlist.map(Number).includes(Number(id))) {
            SN.profile.playlist.push(id);
        }
    
        if (!SN.profile.playlistEntities) SN.profile.playlistEntities = {};
        SN.profile.playlistEntities[id] = pl;
    
        return pl;
    }

    async function SyncPlaylistsAfterLogin() {
        if (!SN.user?.loggedIn || !SN.accessToken) return { ok: false };
    
        LoadProfile();
        if (!SN.playlist) SN.playlist = {};
    
        const pulled = await gja({ a: 'get_playlists' });
        if (pulled && !pulled.error && Array.isArray(pulled.playlists)) {
            if (pulled.limits) SN.playlistLimits = pulled.limits;
            await applyServerPlaylists(pulled.playlists);
            SaveProfile();
            return { ok: true, pulled: pulled.playlists.length };
        }
    
        return { ok: false };
    }

    async function ManualSyncPlaylists() {
        if (!SN.user?.loggedIn || !SN.accessToken) return false;
    
        LoadProfile();
        if (!SN.playlist) SN.playlist = {};
    
        // 1) سرور → لوکال
        const pulled = await gja({ a: 'get_playlists' });
        if (pulled && !pulled.error && Array.isArray(pulled.playlists)) {
            if (pulled.limits) SN.playlistLimits = pulled.limits;
            await applyServerPlaylists(pulled.playlists);
        }
    
        // 2) لوکال‌هایی که هنوز روی سرور نیستند
        const localPayload = [];
        for (const key of Object.keys(SN.playlist)) {
            const pl = SN.playlist[key];
            if (!pl) continue;
    
            const n = Number(pl.id);
            const isLocal = pl.local === true || (n >= 1 && n <= LOCAL_PLAYLIST_MAX);
            if (!isLocal) continue;
    
            localPayload.push({
                local_id: pl.id,
                name: pl.name || ('Playlist ' + pl.id),
                items: (pl.items || []).map((row) => ({
                    id: row.id,
                    type: row.type || 'song'
                }))
            });
        }
    
        if (!localPayload.length) {
            SaveProfile();
            return true;
        }
    
        // 3) push
        const j = await gja({
            a: 'sync_playlists',
            playlists: localPayload
        });
    
        if (!j || j.error) {
            SaveProfile();
            return false;
        }
    
        if (j.limits) SN.playlistLimits = j.limits;
    
        // 4) فقط آن‌هایی که واقعاً ساخته شدند: حذف id لوکال + ingest سرور
        const createdLocalIds = new Set();
        if (Array.isArray(j.created)) {
            for (const row of j.created) {
                const localId = row.local_id;
                const serverPl = row.playlist;
                if (localId == null || !serverPl) continue;
    
                createdLocalIds.add(Number(localId));
    
                delete SN.playlist[localId];
                SN.profile.playlist = (SN.profile.playlist || []).filter(
                    (x) => Number(x) !== Number(localId)
                );
                if (SN.profile.playlistEntities) {
                    delete SN.profile.playlistEntities[localId];
                }
    
                await ingestPlaylistObject(serverPl);
            }
        }
    
        // 5) skip شده‌ها روی دستگاه می‌مانند
        if (Array.isArray(j.skipped)) {
            for (const s of j.skipped) {
                const lid = s.local_id;
                if (lid == null || !SN.playlist[lid]) continue;
                SN.playlist[lid].local = true;
                SN.playlist[lid].cloud = false;
                if (SN.profile.playlistEntities) {
                    SN.profile.playlistEntities[lid] = SN.playlist[lid];
                }
            }
        }
    
        // 6) لیست نهایی سرور را merge کن — بدون پاک کردن همهٔ 1..99
        if (Array.isArray(j.playlists)) {
            await applyServerPlaylists(j.playlists);
        }
    
        SaveProfile();
        return true;
    }
    
    async function ManualSyncAll(cb) {
        try {
            await ManualSyncFavorites();
            await ManualSyncPlaylists();
            await ManualSyncHistory();
            await HydrateProfileItems();
            if (cb) cb(true);
            return true;
        } catch (e) {
            console.error(e);
            if (cb) cb(false);
            return false;
        }
    }

    async function CRPP(q,T) {
        
        if (T !== 'playlist') return;
        const pl = await UPLS(['playlist', q]);
        if (!pl) return;

        const m = pl.meta || {};
        let thumb_item = null;

        const dhc = OA(document.createElement('div'), { className: 'page-detail-header-container' });
        const dtn = OA(document.createElement('div'), { className: 'thumbail' });
        dtn.append(OA(document.createElement('span'), { className: 'placeholder', innerHTML: svg_pls }));
        const thumb = (m && m.tmb) ? m.tmb : null;
        if (thumb) {dtn.append(OA(document.createElement('img'), { src: `${UT}${thumb}.jpg` }));}
        
        const dct = OA(document.createElement('div'), { className: 'content' });
        const tlt = OA(document.createElement('div'), { className: 'title' });
        tlt.append(
            OA(document.createElement('h1'), {
                title: pl.name_fa || pl.name,
                innerHTML: pl.name || pl.name_fa
            })
        );

        const ownerLine = OA(document.createElement('div'), { className: 'artists' });
        if (pl.owner && SN.user && Number(pl.owner) === Number(SN.user.id)) {
            ownerLine.innerHTML = 'By you';
        } else if (pl.owner) {
            ownerLine.innerHTML = 'Playlist';
            // اگر بعداً نام سازنده از سرور آمد: owner_name
            if (pl.owner_name) {
                ownerLine.innerHTML = 'By ' + pl.owner_name;
            }
        }

        const dtl = OA(document.createElement('div'), { className: 'detailes' });
        if (m.des) {
            dtl.append(
                OA(document.createElement('span'), {
                    className: 'playlist-des',
                    innerHTML: m.des
                })
            );
        }
        
        const metaParts = [];
        if (m.nci > 0) metaParts.push(m.nci + (m.nci === 1 ? ' item' : ' items'));
        if (m.ncs > 0) metaParts.push(m.ncs + (m.ncs === 1 ? ' track' : ' tracks'));
        if (m.ncm > 0) metaParts.push(m.ncm + (m.ncm === 1 ? ' music video' : ' music videos'));
        if (m.ncv > 0) metaParts.push(m.ncv + (m.ncv === 1 ? ' video' : ' videos'));
        if (m.ncp > 0) metaParts.push(m.ncp + (m.ncp === 1 ? ' podcast' : ' podcasts'));
        if (metaParts.length) {
            dtl.append(
                OA(document.createElement('span'), {
                    className: 'meta-line',
                    innerHTML: metaParts.join(' · ')
                })
            );
        }
    
        dct.append(tlt, ownerLine, dtl);
        dhc.append(dtn, dct, OA(document.createElement('div'), { className: 'lyric' }));
        MCNT.append(dhc);
        
        // ---- اکشن هدر ----
        const dac = OA(document.createElement('div'), { className: 'page-detail-actions' });
        const dbp = OA(document.createElement('div'), { className: 'play-pause', innerHTML: svg_plys });
        dbp.addEventListener('click', () => {
            if (!pl.items || !pl.items.length) return;
            // از اول لیست
            setPlayQueue(pl.items, pl.items[0].id);
            // بعداً: PlayQueueAt(0)
        });
    
        const isFav = IsFavorite('playlist', pl.id);
        const dbf = OA(document.createElement('div'), {
            className: 'btn btn-model-1 focusable',
            innerHTML: isFav ? svg_fav2 : svg_fav
        });
        dbf.addEventListener('click', () => {
            dbf.innerHTML = ToggleFavorite('playlist', pl.id) ? svg_fav2 : svg_fav;
        });
    
        dac.append(dbp, dbf);
    
        if (isPlaylistOwner(pl)) {
            const ded = OA(document.createElement('div'), {
                className: 'btn btn-model-3',
                innerHTML: 'Edit'
            });
            ded.addEventListener('click', () => {
                CRBW(pl.id, 'playlist', 'edit');
            });
            dac.append(ded);
        }
    
        const dbs = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: svg_sha
        });
        dbs.addEventListener('click', () => {
            copyText(window.location.href, 'Playlist link copied to clipboard');
        });
    
        const morPl = OA(document.createElement('div'), {
            className: 'btn btn-model-1',
            innerHTML: svg_dot
        });
        morPl.addEventListener('click', () => {
            CRBW(pl.id, 'playlist');
        });
    
        dac.append(dbs, morPl);
        MCNT.append(dac);
    
        // ---- لیست آیتم‌ها ----
        if (!Array.isArray(pl.items) || !pl.items.length) {
            ADE();
            return;
        }
    
        const sec = OA(document.createElement('section'), {
            className: 'track-line-container m-top-1'
        });
        const isOwner = isPlaylistOwner(pl);
    
        for (const row of pl.items) {
            const type = row.type;
            let item = null;
            let itemUrl = HS;
    
            if (type === 'song') {
                item = SN.song?.[row.id];
                itemUrl = HS;
            } else if (type === 'podcast') {
                item = SN.podcast?.[row.id];
                itemUrl = HS;
            } else if (type === 'musicvideo') {
                item = SN.musicvideo?.[row.id];
                itemUrl = HV;
            } else if (type === 'video') {
                item = SN.video?.[row.id];
                itemUrl = HV;
            }
    
            if (!item) {
                console.log('CRPP: missing', type, row.id);
                continue;
            }
    
            const artistList = [];
            let primaryArtistUrl = '';
            const artistRefs = Array.isArray(item.artists) ? item.artists : [];
            artistRefs.forEach((ref) => {
                const aid = typeof ref === 'object' ? ref.id : ref;
                if (SN.artist?.[aid]) artistList.push(SN.artist[aid]);
            });
            if (artistList.length) primaryArtistUrl = artistList[0].url || '';
    
            const el = OA(document.createElement('div'), { className: 'track-line' });
    
            const ela = OA(document.createElement('a'), {
                href: itemUrl + item.id + '/' + primaryArtistUrl + '-' + (item.url || '')
            });
            const artwork = OA(document.createElement('div'), {
                className: 'artwork ratio ratio-1x1'
            });
            artwork.append(OA(document.createElement('span'), { className: 'placeholder' }));
            let tmb = item.meta?.tmb;
            if (!tmb && artistList.length === 1) tmb = artistList[0].meta?.tmb || null;
            if (tmb) {
                thumb_item = tmb;
                artwork.append(
                    OA(document.createElement('img'), { src: `${UT}${tmb}.jpg` })
                );
            }
            ela.append(artwork);
            ela.addEventListener('mouseenter', () => UHERO(item, type));
    
            const dpl = OA(document.createElement('div'), {
                className: 'play',
                innerHTML: svg_ply
            });
            dpl.addEventListener('click', () => {
                setPlayQueue(pl.items, item.id);
            });
    
            const dst = OA(document.createElement('div'), { className: 'texts' });
            const det = OA(document.createElement('div'), { className: 'title' });
            const tta = OA(document.createElement('a'), {
                draggable: 'false',
                href: itemUrl + item.id + '/' + primaryArtistUrl + '-' + (item.url || ''),
                innerHTML: item.name || ''
            });
            tta.addEventListener('mouseenter', () => UHERO(item, type));
            det.append(tta);
    
            const dt2 = OA(document.createElement('div'), {
                className: 'text2 artist artists-inline'
            });
            artistList.forEach((artistObj, j) => {
                const artL = OA(document.createElement('a'), {
                    href: HA + artistObj.id + '/' + (artistObj.url || '')
                });
                artL.append(
                    OA(document.createElement('span'), { innerHTML: artistObj.name })
                );
                artL.addEventListener('mouseenter', () => UHERO(artistObj, 'artist'));
                dt2.append(artL);
                if (j < artistList.length - 1) {
                    dt2.append(
                        OA(document.createElement('span'), { innerHTML: ' - ' })
                    );
                }
            });
            dst.append(det, dt2);
    
            const dmt = OA(document.createElement('div'), { className: 'meta' });
            dmt.append(
                OA(document.createElement('div'), {
                    className: 'date',
                    innerHTML: item.meta?.bd || item.meta?.rls || ''
                }),
                OA(document.createElement('div'), { className: 'duration' })
            );
    
            if (isOwner) {
                const dbr = OA(document.createElement('div'), {
                    className: 'btn btn-model-3',
                    innerHTML: 'Remove'
                });
                dbr.addEventListener('click', async () => {
                    if (removeItemFromPlaylist(pl.id, item.id, type)) {
                        await softRefreshPlaylistPage(pl.id);
                    }
                });
                dmt.append(dbr);
            }
    
            const lineParts = [ela, dpl, dst, dmt];
            const dma = OA(document.createElement('div'), { className: 'more-arrow' });
            
            if (isOwner) {
                const btnUp = OA(document.createElement('div'), {
                    className: 'more-btn',
                    innerHTML: svg_aup
                });
                btnUp.addEventListener('click', async () => {
                    if (movePlaylistItem(pl.id, item.id, type, -1)) {
                        await softRefreshPlaylistPage(pl.id);
                    }
                });
                const btnDown = OA(document.createElement('div'), {
                    className: 'more-btn',
                    innerHTML: svg_adw
                });
                btnDown.addEventListener('click', async () => {
                    if (movePlaylistItem(pl.id, item.id, type, +1)) {
                        await softRefreshPlaylistPage(pl.id);
                    }
                });
                dma.append(btnUp, btnDown);
                
            }
            lineParts.push(dma);
            
            const mor = OA(document.createElement('div'), {
                className: 'more',
                innerHTML: svg_dot
            });
            mor.addEventListener('click', () => {
                CRBW(item.id, type, null, { playlistId: pl.id });
            });
            lineParts.push(mor);
    
            el.append(...lineParts);
            sec.append(el);
        }
        
        if (thumb_item) {
            if (!thumb || thumb !== thumb_item) {
                // اگر از قبل img روی dtn هست، عوضش کن؛ وگرنه بساز
                let img = dtn.querySelector('img');
                if (!img) {
                    img = OA(document.createElement('img'), { src: UT + thumb_item + '.jpg' });
                    dtn.append(img);
                } else {
                    img.src = UT + thumb_item + '.jpg';
                }
            }
            if (!pl.meta) pl.meta = {};
            if (pl.meta.tmb !== thumb_item) {
                pl.meta.tmb = thumb_item;
                persistPlaylist(pl);
            }
        }
        
        MCNT.append(sec);
        ADE();
    }
    
    function findPlaylistsContainingItem(itemId, itemType) {
        LoadProfile();
        if (!SN.playlist) return [];
        const out = [];
        for (const pid of (SN.profile.playlist || [])) {
            const pl = SN.playlist[pid];
            if (!pl || !Array.isArray(pl.items)) continue;
            if (pl.items.some((r) => r.id === itemId && r.type === itemType)) {
                out.push(pid);
            }
        }
        return out;
    }
    
    function recalcPlaylistMeta(pl) {
        let ncs = 0, ncp = 0, ncm = 0, ncv = 0;
        for (const row of pl.items || []) {
            if (row.type === 'song') ncs++;
            else if (row.type === 'podcast') ncp++;
            else if (row.type === 'musicvideo') ncm++;
            else if (row.type === 'video') ncv++;
        }
        pl.meta = pl.meta || {};
        pl.meta.nci = (pl.items || []).length;
        ncs ? (pl.meta.ncs = ncs) : delete pl.meta.ncs;
        ncp ? (pl.meta.ncp = ncp) : delete pl.meta.ncp;
        ncm ? (pl.meta.ncm = ncm) : delete pl.meta.ncm;
        ncv ? (pl.meta.ncv = ncv) : delete pl.meta.ncv;
    }
    
    function persistPlaylist(pl) {
        if (!SN.playlist) SN.playlist = {};
        SN.playlist[pl.id] = pl;
        if (!SN.profile.playlistEntities) SN.profile.playlistEntities = {};
        SN.profile.playlistEntities[pl.id] = pl;
        SaveProfile();
    }
    
    /** حذف آیتم از یک پلی‌لیست */
    function removeItemFromPlaylist(playlistId, itemId, itemType) {
        LoadProfile();
        const pl = SN.playlist?.[playlistId];
        if (!pl || !Array.isArray(pl.items)) return false;
    
        const before = pl.items.length;
        pl.items = pl.items.filter(
            (r) => !(r.id === itemId && r.type === itemType)
        );
        if (pl.items.length === before) return false;
    
        // position را 0..n-1 یکدست کن
        pl.items.forEach((r, i) => { r.position = i; });
        recalcPlaylistMeta(pl);
        persistPlaylist(pl);
    
        // سرور
        const n = Number(playlistId);
        const isLocal = pl.local || (n >= 1 && n <= LOCAL_PLAYLIST_MAX);
        if (SN.user?.loggedIn && SN.accessToken && !isLocal) {
            gja({
                a: 'remove_playlist_item',
                playlist_id: playlistId,
                id: itemId,
                type: itemType
            }).catch(() => {});
        }
        return true;
    }
    
    /** جابه‌جایی پوزیشن: dir = -1 بالا، +1 پایین */
    function movePlaylistItem(playlistId, itemId, itemType, dir) {
        LoadProfile();
        const pl = SN.playlist?.[playlistId];
        if (!pl || !Array.isArray(pl.items)) return false;
    
        const idx = pl.items.findIndex(
            (r) => r.id === itemId && r.type === itemType
        );
        if (idx < 0) return false;
    
        const j = idx + dir;
        if (j < 0 || j >= pl.items.length) return false;
    
        const tmp = pl.items[idx];
        pl.items[idx] = pl.items[j];
        pl.items[j] = tmp;
        pl.items.forEach((r, i) => { r.position = i; });
        persistPlaylist(pl);
    
        pushPlaylistWindowToServer(pl);
        return true;
    }
    
    /** پنجرهٔ ابر: کل items مرتب؛ سرور خودش سقف را می‌بُرد */
    function pushPlaylistWindowToServer(pl) {
        if (!pl || !SN.user?.loggedIn || !SN.accessToken) return;
        const n = Number(pl.id);
        if (pl.local || (n >= 1 && n <= LOCAL_PLAYLIST_MAX)) return;
    
        gja({
            a: 'set_playlist_items',
            playlist_id: pl.id,
            items: (pl.items || []).map((r) => ({
                id: r.id,
                type: r.type || 'song'
            }))
        }).then((j) => {
            if (j && j.limits) SN.playlistLimits = j.limits;
            if (j && j.capped) pl.syncCapped = true;
        }).catch(() => {});
    }
    
    /** رفرش نرم صفحهٔ پلی‌لیست فعلی بدون API */
    async function softRefreshPlaylistPage(playlistId) {
        // اگر الان روی همین پلی‌لیست هستیم
        const path = location.pathname || '';
        if (!path.includes('/playlist/')) return;
        // chp بدون هل دادن تاریخچهٔ اضافه — یا همان CRPP
        MCNT.innerHTML = '';
        await CRPP(playlistId, 'playlist');
    }
    
    async function deletePlaylistCompletely(playlistId) {
        LoadProfile();
        const pl = SN.playlist?.[playlistId];
        if (!pl) return false;
    
        const n = Number(playlistId);
        const isLocal = pl.local || (n >= 1 && n <= LOCAL_PLAYLIST_MAX);
    
        // remove from Local
        delete SN.playlist[playlistId];
        SN.profile.playlist = (SN.profile.playlist || []).filter(
            (id) => Number(id) !== Number(playlistId)
        );
        if (SN.profile.playlistEntities) {
            delete SN.profile.playlistEntities[playlistId];
        }
        
        // if in favorite.playlist
        if (SN.profile.favorite?.playlist) {
            SN.profile.favorite.playlist = SN.profile.favorite.playlist.filter(
                (id) => Number(id) !== Number(playlistId)
            );
        }
        
        SaveProfile();
    
        if (SN.user?.loggedIn && SN.accessToken && !isLocal) {
            await gja({ a: 'delete_playlist', playlist_id: playlistId });
        }
    
        return true;
    }
    
    function isPlaylistOwner(pl) {
        if (!pl) return false;
    
        const n = Number(pl.id);
        if (pl.local === true || (n >= 1 && n <= LOCAL_PLAYLIST_MAX)) {
            return true;
        }
        if (pl.system) return false;
    
        if (!SN.user?.loggedIn || SN.user.id == null) return false;
        if (pl.owner == null) return false;
    
        return Number(pl.owner) === Number(SN.user.id);
    }

// =============================================
// 1. اول تابع تشخیص قابلیت
// =============================================
function canUseLocalStorage() {
  try {
    const t = '__t__';
    localStorage.setItem(t, t);
    localStorage.removeItem(t);
    return true;
  } catch (e) {
    return false;
  }
}

// =============================================
// 2. بلافاصله مقداردهی
// =============================================
const HAS_LOCAL = canUseLocalStorage();
const PROFILE_KEY = 'ha_profile';

// =============================================
// 3. بعد توابعی که از HAS_LOCAL استفاده می‌کنند
// =============================================

    function LoadProfile() {
        if (SN.profile) return SN.profile;
    
        let data = null;
        if (HAS_LOCAL) {
            try {
                const raw = localStorage.getItem(PROFILE_KEY);
                if (raw) data = JSON.parse(raw);
            } catch (e) {}
        }
    
        const emptyFavorite = {};
        for (const t of ITEM_TYPES) {
            emptyFavorite[t] = [];
        }
    
        SN.profile = {
            favorite: emptyFavorite,
            playlist: [],
            history: [],   // [{ id, type, at }]
            search: []     // ['query', ...] یا [{ q, at }]
        };
    
        if (data?.favorite) {
            for (const t of ITEM_TYPES) {
                if (Array.isArray(data.favorite[t])) {
                    SN.profile.favorite[t] = data.favorite[t];
                }
            }
        }
    
        if (!SN.playlist || typeof SN.playlist !== 'object') {
            SN.playlist = {};
        }
    
        if (data?.playlistEntities && typeof data.playlistEntities === 'object') {
            SN.profile.playlistEntities = data.playlistEntities;
            Object.keys(data.playlistEntities).forEach((id) => {
                SN.playlist[id] = data.playlistEntities[id];
            });
        } else {
            SN.profile.playlistEntities = {};
        }
    
        if (Array.isArray(data?.playlist)) {
            SN.profile.playlist = data.playlist;
        }
    
        // history: نرمال به { id, type, at }
        if (Array.isArray(data?.history)) {
            SN.profile.history = data.history
                .map((row) => {
                    if (row == null) return null;
                    if (typeof row === 'object' && row.id != null) {
                        return {
                            id: row.id,
                            type: row.type || 'song',
                            at: row.at || 0
                        };
                    }
                    // سازگاری با آرایهٔ فقط id قدیمی
                    return { id: row, type: 'song', at: 0 };
                })
                .filter(Boolean);
        }
    
        if (Array.isArray(data?.search)) {
            SN.profile.search = data.search;
        }
    
        return SN.profile;
    }
    
    function SaveProfile() {
        if (!SN.profile || !HAS_LOCAL) return;
    
        SN.profile.playlistEntities = {};
        (SN.profile.playlist || []).forEach((id) => {
            if (SN.playlist && SN.playlist[id]) {
                SN.profile.playlistEntities[id] = SN.playlist[id];
            }
        });
    
        try {
            localStorage.setItem(PROFILE_KEY, JSON.stringify(SN.profile));
        } catch (e) {
            console.warn('SaveProfile failed:', e.message);
        }
    }
    
    function pushPlayHistory(id, type) {
        if (id == null || !type) return;
        if (PLAYABLE_TYPES.indexOf(type) === -1) return;
    
        const at = Math.floor(Date.now() / 1000);
    
        LoadProfile();
        if (!Array.isArray(SN.profile.history)) SN.profile.history = [];
    
        // حذف تکراری همین id+type
        SN.profile.history = SN.profile.history.filter(
            (row) => !(Number(row.id) === Number(id) && row.type === type)
        );
    
        SN.profile.history.unshift({ id: id, type: type, at: at });
    
        // سقف ۵۰ فقط برای همین type (بقیه typeها دست نخورند)
        let countThisType = 0;
        SN.profile.history = SN.profile.history.filter((row) => {
            if (row.type !== type) return true;
            countThisType++;
            return countThisType <= HISTORY_MAX;
        });
    
        SaveProfile();
    
        // اگر لاگین است → صف ارسال
        enqueueHistoryForServer(id, type, at);
    }

    function enqueueHistoryForServer(id, type, at) {
        if (!SN.user?.loggedIn || !SN.accessToken) return;
    
        if (!SN._pendingHistory) SN._pendingHistory = [];
    
        SN._pendingHistory = SN._pendingHistory.filter(
            (r) => !(Number(r.id) === Number(id) && r.type === type)
        );
        SN._pendingHistory.push({ id: id, type: type, at: at });
    
        maybeFlushHistory(false);
    }
    
    function maybeFlushHistory(force) {
        if (!SN.user?.loggedIn || !SN.accessToken) return;
    
        const pending = SN._pendingHistory || [];
        if (!pending.length) return;
    
        const now = Date.now();
        if (!SN._lastHistoryFlushAt) SN._lastHistoryFlushAt = 0;
    
        const enoughItems = pending.length >= HISTORY_FLUSH_N;
        const enoughTime = (now - SN._lastHistoryFlushAt) >= HISTORY_FLUSH_MS;
    
        if (!force && !enoughItems && !enoughTime) return;
    
        flushPlayHistory();
    }
    
    async function flushPlayHistory() {
        const pending = SN._pendingHistory || [];
        if (!pending.length) return;
        if (SN._historyFlushing) return;
    
        SN._historyFlushing = true;
        const batch = pending.slice();
    
        try {
            const j = await gja({ a: 'sync_history', items: batch });
            if (j && !j.error) {
                SN._pendingHistory = (SN._pendingHistory || []).filter((r) => {
                    return !batch.some(
                        (b) => Number(b.id) === Number(r.id) && b.type === r.type
                    );
                });
                SN._lastHistoryFlushAt = Date.now();
            }
        } catch (e) {
            console.warn('flushPlayHistory', e);
        } finally {
            SN._historyFlushing = false;
        }
    }
    
    function startHistoryFlushTimer() {
        if (SN._historyFlushTimer) return;
        SN._historyFlushTimer = setInterval(function () {
            maybeFlushHistory(false);
        }, HISTORY_FLUSH_MS);
    }
    
    function stopHistoryFlushTimer() {
        if (SN._historyFlushTimer) {
            clearInterval(SN._historyFlushTimer);
            SN._historyFlushTimer = null;
        }
    }
    
    if (!window._haHistoryVisBound) {
        window._haHistoryVisBound = true;
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                maybeFlushHistory(true);
            }
        });
    }

    /** سرچ لوکال */
    function pushSearch(q) {
        q = String(q || '').trim();
        if (!q) return;
    
        LoadProfile();
        if (!Array.isArray(SN.profile.search)) SN.profile.search = [];
    
        SN.profile.search = SN.profile.search.filter(
            (x) => String(typeof x === 'object' ? x.q : x).toLowerCase() !== q.toLowerCase()
        );
        SN.profile.search.unshift(q);
        if (SN.profile.search.length > SEARCH_MAX) {
            SN.profile.search.length = SEARCH_MAX;
        }
        SaveProfile();
    }
    
    function IsFavorite(type, id) {
      LoadProfile();
      const list = SN.profile.favorite[type];
      return Array.isArray(list) && list.includes(id);
    }

    function ToggleFavorite(type, id) {
        LoadProfile();
    
        if (!SN.profile.favorite[type]) {
            SN.profile.favorite[type] = [];
        }
    
        const list = SN.profile.favorite[type];
        const index = list.indexOf(id);
        let nowFavorite;
    
        if (index === -1) {
            list.push(id);
            nowFavorite = true;
        } else {
            list.splice(index, 1);
            nowFavorite = false;
        }
    
        SaveProfile();
    
        // UI همان لحظه وضعیت جدید را دارد؛ سرور در پس‌زمینه
        if (SN.user?.loggedIn && SN.accessToken) {
            gja({
                p: type,
                a: nowFavorite ? 'fav_add' : 'fav_remove',
                q: [id]
            }).catch(err => {
                console.warn('favorite sync failed:', type, id, err.message);
            });
        }
    
        return nowFavorite; // boolean برای UI
    }
    
    async function ToggleFavoriteAsync(type, id) {
        const nowFavorite = ToggleFavorite(type, id); // لوکال + fire-and-forget سرور
        return nowFavorite;
    }

    async function syncFavoriteToServer(type, id, isAdd) {
        try {
            await gja({
                p: type,
                a: isAdd ? 'fav_add' : 'fav_remove',
                q: [id]
            });
        } catch (err) {
            console.warn('syncFavoriteToServer failed:', type, id, err.message);
        }
    }

    function rollbackFavorite(type, id, wasAdd) {
        LoadProfile();
        const list = SN.profile.favorite[type] || [];
        const index = list.indexOf(id);
    
        if (wasAdd && index !== -1) {
            list.splice(index, 1);
        } else if (!wasAdd && index === -1) {
            list.push(id);
        }
    
        SaveProfile();
    }

    async function fetchServerFavorites() {
        try {
            const j = await gja({ a: 'get_favorites' });
            return j?.favorites || null;
        } catch (err) {
            console.warn('fetchServerFavorites failed:', err.message);
            return null;
        }
    }
    
    async function SoftPullFavorites() {
        if (!SN.user?.loggedIn || !SN.accessToken) return;
    
        const serverFav = await fetchServerFavorites();
        if (serverFav) {
            mergeServerFavoritesIntoLocal(serverFav);
        }
    }
    
    function mergeServerFavoritesIntoLocal(serverFav) {
        LoadProfile();
        if (!serverFav) return 0;
    
        let added = 0;
        const types = Object.keys(SN.profile.favorite);
    
        for (const type of types) {
            const serverList = Array.isArray(serverFav[type]) ? serverFav[type] : [];
            if (!SN.profile.favorite[type]) SN.profile.favorite[type] = [];
    
            for (const id of serverList) {
                if (!SN.profile.favorite[type].includes(id)) {
                    SN.profile.favorite[type].push(id);
                    added++;
                }
            }
        }
    
        if (added > 0) SaveProfile();
        return added;
    }
    
    function findLocalOnlyFavorites(serverFav) {
        LoadProfile();
        const localOnly = {};
        let count = 0;
    
        const types = Object.keys(SN.profile.favorite);
        for (const type of types) {
            const localList = SN.profile.favorite[type] || [];
            const serverList = Array.isArray(serverFav?.[type]) ? serverFav[type] : [];
            const only = localList.filter(id => !serverList.includes(id));
            if (only.length) {
                localOnly[type] = only;
                count += only.length;
            }
        }
    
        return { localOnly, count };
    }
    
    async function pushLocalFavoritesToServer(items) {
        try {
            const j = await gja({
                a: 'sync_favorites',
                items: items || SN.profile.favorite
            });
    
            // لیست نهایی سرور را دوباره روی لوکال بنشین
            if (j?.favorites) {
                mergeServerFavoritesIntoLocal(j.favorites);
            }
            return j;
        } catch (err) {
            console.warn('pushLocalFavoritesToServer failed:', err.message);
            return null;
        }
    }
    
    /**
     * بعد از لاگین صدا بزن
     */
    async function SyncFavoritesAfterLogin() {
        if (!SN.user?.loggedIn || !SN.accessToken) return;
    
        const serverFav = await fetchServerFavorites();
        if (!serverFav) return;
    
        // 1) سرور → لوکال
        mergeServerFavoritesIntoLocal(serverFav);
    
        // 2) لوکال‌هایی که سرور ندارد
        const { localOnly, count } = findLocalOnlyFavorites(serverFav);
    
        if (count > 0) {
            ShowFavoriteSyncSheet(count, localOnly);
        }
    }
    
    async function ManualSyncFavorites() {
        if (!SN.user?.loggedIn || !SN.accessToken) return false;
    
        const serverFav = await fetchServerFavorites();
        if (serverFav) {
            mergeServerFavoritesIntoLocal(serverFav);
        }
    
        const j = await pushLocalFavoritesToServer(SN.profile.favorite);
        return !!j;
    }

    async function userAction(localFirst, serverPayload) {
        const localResult = localFirst ? localFirst() : null;
    
        // مهمان → فقط لوکال
        if (!SN.user?.loggedIn || !SN.accessToken) {
            return { ok: true, local: localResult, server: null, offline: true };
        }
    
        try {
            const j = await gja(serverPayload);
            return { ok: true, local: localResult, server: j, offline: false };
        } catch (err) {
            console.warn('userAction failed:', err.message);
            return { ok: false, local: localResult, server: null, offline: false, error: err.message };
        }
    }

    async function ToggleLike(id, type) {
        try {
            const j = await gja({ p: type, a: 'like', q: [id] });
            return j;
        } catch (err) {
            console.warn('ToggleLike failed:', type, id, err.message);
            return null;
        }
    }

    function hasLocalDataToSync() {
        LoadProfile();
    
        // فاوریت لوکال
        let hasFav = false;
        const fav = SN.profile?.favorite || {};
        for (const t of ITEM_TYPES || Object.keys(fav)) {
            if (Array.isArray(fav[t]) && fav[t].length) {
                hasFav = true;
                break;
            }
        }
    
        // پلی‌لیست لوکال (id 1..99 یا local:true)
        let hasPl = false;
        const ids = SN.profile?.playlist || [];
        for (const id of ids) {
            const n = Number(id);
            const pl = SN.playlist?.[id];
            if (pl?.local || (n >= 1 && n <= (LOCAL_PLAYLIST_MAX || 99))) {
                hasPl = true;
                break;
            }
        }
    
        return hasFav || hasPl;
    }

    async function CRSR(q = '', a = 1) {
        if (!SN.search || typeof SN.search !== 'object' || Array.isArray(SN.search)) {
            SN.search = {};
        }
    
        q = String(q || '').trim();
    
        function hasContent(key) {
            return (
                SN.search[key] &&
                Array.isArray(SN.search[key]) &&
                SN.search[key].length > 0
            );
        }
    
        function renderRecentSearches() {
            LoadProfile();
            const list = Array.isArray(SN.profile.search) ? SN.profile.search : [];
            if (!list.length) return;
    
            const hed = OA(document.createElement('div'), {
                className: 'section-header m-top-1'
            });
            hed.append(
                OA(document.createElement('h2'), {
                    className: 'title',
                    innerHTML: 'Recent Searches'
                })
            );
            MCNT.append(hed);
    
            const box = OA(document.createElement('div'), {
                className: 'search-recent m-top-1'
            });
    
            list.forEach((item) => {
                const term = typeof item === 'object' ? (item.q || '') : String(item);
                if (!term) return;
    
                const btn = OA(document.createElement('div'), {
                    className: 'btn btn-model-3 focusable search-recent-item',
                    innerHTML: term
                });
                btn.addEventListener('click', () => {
                    chp(HSR + encodeURIComponent(term));
                });
                box.append(btn);
            });
    
            MCNT.append(box);
        }
    
        function renderUI() {
            const dsc = OA(document.createElement('div'), { className: 'search-container m-top-1' });
            const dcc = OA(document.createElement('div'), { className: 'content' });
            dsc.append(dcc);
    
            const dci = OA(document.createElement('div'), { className: 'icon', innerHTML: svg_src });
            const dsf = OA(document.createElement('form'));
    
            const ifs = OA(document.createElement('input'), {
                className: 'input',
                type: 'text',
                id: 'search',
                maxlength: 100,
                placeholder: 'Search songs, artists, albums ...',
                autocorrect: 'off',
                autocapitalize: 'off',
                spellcheck: 'false',
                value: q || (SN.search.q || '')
            });
    
            dsf.append(ifs);
    
            // جلوگیری از رفرش فرم
            dsf.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const v = ifs.value.trim();
                if (v) chp(HSR + encodeURIComponent(v));
            });
    
            const dsb = OA(document.createElement('button'), {
                className: 'clear btn btn-model-2',
                type: 'submit',
                innerHTML: svg_sfb
            });
    
            dcc.append(dci, dsf, dsb);
            MCNT.append(dsc);
    
            // بدون کوئری → فقط recent
            if (!q) {
                renderRecentSearches();
                ADE();
                return;
            }
    
            if (SN.search.q) {
                const srd = OA(document.createElement('div'), { className: 'page-header m-top-1' });
                const srh = OA(document.createElement('h1'), {
                    className: 'title',
                    innerHTML: 'Search results for "' + SN.search.q + '"'
                });
                srd.append(srh);
                MCNT.append(srd);
            }
    
            const searchTypes = [
                {
                    key: 'songs',
                    tabKey: 'tracks',
                    label: 'Tracks',
                    renderResult: () => CRSC(SN.search.songs, 'song'),
                    renderAll: () => CRGL(SN.search.songs, 'song')
                },
                {
                    key: 'musicvideos',
                    tabKey: 'musicvideos',
                    label: 'Music Videos',
                    renderResult: () => CRSC(SN.search.musicvideos, 'musicvideo'),
                    renderAll: () => CRGC(SN.search.musicvideos, 'musicvideo')
                },
                {
                    key: 'artists',
                    tabKey: 'artists',
                    label: 'Artists',
                    renderResult: () => CRSC(SN.search.artists, 'artist'),
                    renderAll: () => CRGC(SN.search.artists, 'artist')
                },
                {
                    key: 'podcasts',
                    tabKey: 'podcasts',
                    label: 'Podcasts',
                    renderResult: () => CRSC(SN.search.podcasts, 'podcast'),
                    renderAll: () => CRGL(SN.search.podcasts, 'podcast')
                },
                {
                    key: 'albums',
                    tabKey: 'albums',
                    label: 'Albums',
                    renderResult: () => CRSC(SN.search.albums, 'album'),
                    renderAll: () => CRGC(SN.search.albums, 'album')
                },
                {
                    key: 'playlists',
                    tabKey: 'playlists',
                    label: 'Playlists',
                    renderResult: () => CRSC(SN.search.playlists, 'playlist'),
                    renderAll: () => CRGC(SN.search.playlists, 'playlist')
                },
                {
                    key: 'videos',
                    tabKey: 'videos',
                    label: 'Videos',
                    renderResult: () => CRSC(SN.search.videos, 'video'),
                    renderAll: () => CRGC(SN.search.videos, 'video')
                }
            ];
    
            const availableTypes = searchTypes.filter((type) => hasContent(type.key));
    
            if (availableTypes.length === 0) {
                const der = OA(document.createElement('div'), { className: 'error-message-container' });
                der.append(
                    OA(document.createElement('div'), {
                        className: 'text1 m-top-1-5',
                        innerHTML: 'No Found!'
                    }),
                    OA(document.createElement('div'), {
                        className: 'text2',
                        innerHTML: 'It is better to use short words and letters and numbers.'
                    })
                );
                MCNT.append(der);
                ADE();
                return;
            }
    
            const requestedType = availableTypes.find((type) => type.tabKey == a);
            const effectiveTab = requestedType ? a : 1;
    
            const hed = OA(document.createElement('div'), { className: 'main-tabs' });
    
            hed.append(
                OA(document.createElement('a'), {
                    className: 'item' + (effectiveTab == 1 ? ' active' : ''),
                    href: HSR + encodeURIComponent(q),
                    innerHTML: 'Result'
                })
            );
    
            availableTypes.forEach((type) => {
                hed.append(
                    OA(document.createElement('a'), {
                        className: 'item' + (effectiveTab == type.tabKey ? ' active' : ''),
                        href: HSR + encodeURIComponent(q) + '/' + type.tabKey,
                        innerHTML: type.label
                    })
                );
            });
    
            MCNT.append(hed);
    
            if (effectiveTab == 1) {
                availableTypes.forEach((type) => {
                    const sh = OA(document.createElement('div'), {
                        className: 'section-header m-top-3'
                    });
                    sh.append(
                        OA(document.createElement('h2'), {
                            className: 'title',
                            innerHTML: type.label
                        })
                    );
                    MCNT.append(sh);
                    type.renderResult();
                });
            } else {
                const selectedType = availableTypes.find((type) => type.tabKey == effectiveTab);
                if (selectedType) selectedType.renderAll();
            }
    
            ADE();
        }
    
        // ---- بدون کوئری ----
        if (!q) {
            renderUI();
            return;
        }
    
        // ---- کش ----
        if (SN.search.q === q) {
            pushSearch(q); // حتی از recent
            renderUI();
            return;
        }
    
        try {
            await gjs(['search', q]);
            if (!SN.search || SN.search.q !== q) return;
    
            pushSearch(q); // ثبت در SN.profile.search + local
            renderUI();
        } catch (error) {
            console.error('CRSR: search failed:', error);
        }
    }
    
    async function gsa(q) {
 
        try {
            const res = await fetch(HP+'?_qjs=adm&q='+q);
            if (!res.ok) {
              throw new Error(`Response status: ${response.status}`);
            }
            const jd = await res.json();

            if(jd.ok==true){
                SN['add']=jd;
                if(SN.add.add_artist=='success' || SN.add.add_album=='success' || SN.add.add==true){CRAD();}
            }
            console.log(SN);

        } catch (error) {
            console.error(error.message);
        }

    }
    
    async function CRAD(qs){
        
        (self._next.add = self._next.add || {});
        const e = SN.add;

        console.log(qs);console.log(self._next);
        
        MCNT.innerHTML = '';
    
        const dhc = OA(document.createElement("div"), {className: "page-detail-header-container"});        
        const edt = OA(document.createElement("div"), {className: "editor-container"});
        const err = OA(document.createElement("span"), {className: "error", innerHTML: (e.err || '')});

        dhc.append(edt);
    
        const dsf = OA(document.createElement("form"));
        
        const df1 = OA(document.createElement("div"), {className: "input-container"});
        const ian = OA(document.createElement("input"), {className: "input" ,type: "text",id:"artist" ,value:(e.artist || '') ,maxlength: 150, placeholder:"Artist", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        const sar = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Artist"});
        const sr1 = OA(document.createElement("span"), {className: "artist_serach true_false" });        
        df1.append(ian,sar,sr1);
        
        const baa = OA(document.createElement("span"), {className: "clear btn btn-model-2" ,innerHTML: "Add", id:"add_artist"});

        dsf.append(df1,baa);
        
        const drt = OA(document.createElement("div"), {className: "artist vis-none"});
        const df2 = OA(document.createElement("div"), {className: "input-container"});
        const iaf = OA(document.createElement("input"), {className: "input" ,type: "text",id:"artist_fa" ,value:(e.artist_fa || '') ,maxlength: 150, placeholder:"Artist Farsi Name", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        const saf = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Artist Farsi Name"});
        df2.append(iaf,saf);
        
        const df3 = OA(document.createElement("div"), {className: "input-container"});
        const iat = OA(document.createElement("input"), {className: "input" ,type: "text",id:"artist_thumb" ,value:(e.artist_thumb || '') , placeholder:"Thumb of Artist", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        const sat = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Thumb of Artist"});
        const sim = OA(document.createElement("span"), {className: "true_false"});  
        const svi = OA(document.createElement("svg"), {className: "placeholder", id:"img_artist_thumb", width: 48, });
        const ima = OA(document.createElement("img"), {className: "placeholder", id:"img_artist_thumb", width: 52, src:"/thumb/remove-image.svg"});
        sim.append(ima);
        df3.append(iat,sat,sim);
    
        const df4 = OA(document.createElement("div"), {className: "input-container"});
        const iab = OA(document.createElement("input"), {className: "input" ,type: "date",id:"artist_bd" ,value:(e.artist_bd || '') });
        const abd = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Birthday"});
        df4.append(iab,abd);
        
        const df5 = OA(document.createElement("div"), {className: "input-container"});
        const aac = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Action:"});
        const aca = OA(document.createElement("select"), {className: "input" ,id:"atrist_act"});
        const ap1 = OA(document.createElement("option"), {className: "placeholder",value: 1, innerHTML: "Normal",selected: (e.song_act==1)?"selected":''});
        const ap2 = OA(document.createElement("option"), {className: "placeholder",value: 2, innerHTML: "Farsi" ,selected: (e.song_act==2)?"selected":''});
        const ap3 = OA(document.createElement("option"), {className: "placeholder",value: 3, innerHTML: "Under" ,selected: (e.song_act==2)?"selected":''});
        const ap4 = OA(document.createElement("option"), {className: "placeholder",value: 4, innerHTML: "World" ,selected: (e.song_act==3)?"selected":''});
        aca.append(ap1,ap2,ap3,ap4);
        df5.append(aca,aac);
    
        const asb = OA(document.createElement("button"), {className: "clear btn btn-model-2", innerHTML: "Add New Artist", id:"sub_artist", type: "submit", value:"Submit"});
        drt.append(df2,df3,df4,df5,asb);
        dsf.append(drt);
    
        const dlm = OA(document.createElement("div"), {className: "album vis-none"});
        const df6 = OA(document.createElement("div"), {className: "input-container"});
        const iln = OA(document.createElement("input"), {className: "input" ,type: "text",id:"album" ,value:(e.album || '') ,maxlength: 150, placeholder:"Album Or Single", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        const dtl = OA(document.createElement("datalist"), {className: "editor-label",id:"albumlist"}); 
        const sln = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Album:"});
        const ilc = OA(document.createElement("input"), {className: "checkmark vis-none" ,type: "checkbox",id:"single" });
        const sr2 = OA(document.createElement("label"), {className: "artist_serach true_false", htmlFor:"single", innerHTML: "Single" });  
        df6.append(iln,dtl,sln,ilc,sr2);
        
        const bla = OA(document.createElement("span"), {className: "clear btn btn-model-2" ,innerHTML: "Add", id:"add_album"});
        dlm.append(df6,bla);
        
        const dlt = OA(document.createElement("div"), {className: "album_add vis-none"});
        const df16 = OA(document.createElement("div"), {className: "input-container"});
        const ilf = OA(document.createElement("input"), {className: "input" ,type: "text",id:"album_fa" ,value:(e.album_fa || '') ,maxlength: 150, placeholder:"Album Farsi Name", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        const slf = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Album Farsi Name"});
        df16.append(ilf,slf);
        
        const df17 = OA(document.createElement("div"), {className: "input-container"});        
        const lst = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Thumb of Album:"});
        const ilt = OA(document.createElement("input"), {className: "input" ,type: "text",id:"album_thumb" ,value:(e.album_thumb || '') , placeholder:"Thumb of Album", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        const sp6 = OA(document.createElement("span"), {className: "true_false"});
        const iml = OA(document.createElement("img"), {className: "placeholder", id:"img_album_thumb", width: 52, src:"/thumb/remove-image.svg"});
        sp6.append(iml);
        df17.append(ilt,lst,sp6);
        
        const df18 = OA(document.createElement("div"), {className: "input-container"});
        const lbd = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Create Date:"});
        const ilb = OA(document.createElement("input"), {className: "input" ,type: "date",id:"album_rd" ,value:(e.album_rd || '') ,maxlength: 150, placeholder:"Create Date", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        df18.append(ilb,lbd);
        
        const alb = OA(document.createElement("button"), {className: "clear btn btn-model-2", innerHTML: "Add New Album", id:"sub_album", type: "submit", value:"Submit"});
        
        dlt.append(df16,df17,df18,alb);
        dlm.append(dlt);
        
        const df7 = OA(document.createElement("div"), {className: "input-container track_num vis-none"});
        const slt = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Track Number:"});
        const int = OA(document.createElement("input"), {className: "input" ,type: "text",id:"track_num" ,value:(e.album || '') , placeholder:"Track Number", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        df7.append(int,slt);
        
        
        
        
        dlm.append(df7);
        
        dsf.append(dlm);
        
        const dsg = OA(document.createElement("div"), {className: "song vis-none"});


        const df8 = OA(document.createElement("div"), {className: "input-container"});
        const ssn = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Song Name:"});
        const isn = OA(document.createElement("input"), {className: "input" ,type: "text",id:"song" ,value:(e.song || '') ,placeholder:"Song Name", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        df8.append(isn,ssn);
       
        const df9 = OA(document.createElement("div"), {className: "input-container"});         
        const ssf = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Song Name Fa: "});
        const isf = OA(document.createElement("input"), {className: "input" ,type: "text",id:"song_fa" ,value:(e.song_fa || '') , placeholder:"Song Name Fa", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        df9.append(isf,ssf);
       
        const df10 = OA(document.createElement("div"), {className: "input-container"});        
        const sst = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Thumb of Song:"});
        const ist = OA(document.createElement("input"), {className: "input" ,type: "text",id:"song_thumb" ,value:(e.song_thumb || '') , placeholder:"Thumb of Song", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        const sp1 = OA(document.createElement("span"), {className: "true_false"});
        const imt = OA(document.createElement("img"), {className: "placeholder", id:"img_song_thumb", width: 52, src:"/thumb/remove-image.svg"});
        sp1.append(imt);
        df10.append(ist,sst,sp1);
        

        
        const df11 = OA(document.createElement("div"), {className: "input-container"});
        const sfu = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Song File:"});
        const isu = OA(document.createElement("input"), {className: "input" ,type: "text",id:"song_file" ,value:(e.song_file || '') ,placeholder:"Song File", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        df11.append(isu,sfu);
  
        const asr = OA(document.createElement("audio"), {className: "input" ,id:"song_audio"});
        
        const df12 = OA(document.createElement("div"), {className: "input-container"});
        const sfc = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Copy In Host:"});
        const isc = OA(document.createElement("input"), {className: "vis-none" ,type: "checkbox",id:"song_copy"});
        const lb3 = OA(document.createElement("label"), {className: "artist_serach true_false", htmlFor:"song_copy", innerHTML: "Copy To Host" });  
        df12.append(isc,lb3,sfc);

        const df13 = OA(document.createElement("div"), {className: "input-container"});
        const sbd = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Create Date:"});
        const isb = OA(document.createElement("input"), {className: "input" ,type: "date",id:"song_rd" ,value:(e.song_rd || '') ,maxlength: 150, placeholder:"Create Date", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        df13.append(isb,sbd);

        const df14 = OA(document.createElement("div"), {className: "input-container"});     
        const sac = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Action:"});
        const act = OA(document.createElement("select"), {className: "input" ,id:"song_act"});
        const op1 = OA(document.createElement("option"), {className: "placeholder",value: 1, innerHTML: "Normal",selected: (e.song_act==1)?"selected":''});
        const op2 = OA(document.createElement("option"), {className: "placeholder",value: 2, innerHTML: "Farsi" ,selected: (e.song_act==2)?"selected":''});
        const op3 = OA(document.createElement("option"), {className: "placeholder",value: 3, innerHTML: "World" ,selected: (e.song_act==3)?"selected":''});
        act.append(op1,op2,op3);
        df14.append(act,sac);

        const df15 = OA(document.createElement("div"), {className: "input-container"});       
        const sbt = OA(document.createElement("label"), {className: "editor-label", innerHTML: "Bitrate:"});
        const bit = OA(document.createElement("select"), {className: "input" ,id:"song_bit"});
        const bt1 = OA(document.createElement("option"), {className: "placeholder",value: 128, innerHTML: "128",selected: (e.song_bit==128)?"selected":''});
        const bt2 = OA(document.createElement("option"), {className: "placeholder",value: 320, innerHTML: "320" ,selected: (e.song_bit==320)?"selected":''});
        bit.append(bt1,bt2);
        df15.append(bit,sbt);
        dsg.append(df8,df9,df10,df11,asr,df12,df13,df14,df15);  
        const fsb = OA(document.createElement("button"), {className: "clear btn btn-model-2", innerHTML: "Add Song", id:"add_song", type: "submit", value:"Submit"});
        dsg.append(fsb,err);
        
        dsf.append(dsg);
        edt.append(dsf);

        const tmp = OA(document.createElement("div"), {className: "temp_song vis-none"});
        const pis = OA(document.createElement("img"), {className: "placeholder", id:"img_artist_thumb", width: 52, src:"/thumb/"+e.thumb+".jpg"});
        const aan = OA(document.createElement("a"), {className: "placeholder", innerHTML: e.artist ,href:(e.artist)?HA+e.artist.replace(' ','_'):''});
        const asn = OA(document.createElement("a"), {className: "placeholder", innerHTML: e.song , href:(e.song)?HS+e.artist.replace(" ", "_")+"-"+e.song.replace(" ", "_"):''});
        const ast = OA(document.createElement("audio"), {className: "input" ,id:"temp_audio",controls:'controls' ,src:e.song_file });
        const bns = OA(document.createElement("span"), {className: "clear btn btn-model-2" ,innerHTML: "Add New", id:"add_new"});

        tmp.append(pis,pis,aan,asn,ast,bns);  
        
        dhc.append(tmp);        
        MCNT.append(dhc);        

        ////
        let QS = {};
        var CAS = document.querySelector(".temp_song");
        
        var ADF = document.querySelector(".editor-container form");
        const ERR = ADF.querySelector(".error");
        const ART = ADF.querySelector("#artist");
        const SR1 = ADF.querySelector(".artist_serach");
        const AAT = ADF.querySelector(".artist");
        const BBA = ADF.querySelector("#add_artist");
        
        const BLA = ADF.querySelector("#add_album");
        const DLA = ADF.querySelector(".album_add");
        const SUL = ADF.querySelector("#sub_album");
    
        const LBM = ADF.querySelector(".album");
        const DTL = ADF.querySelector("#albumlist");
        const LCS = ADF.querySelector("#single");
        const LBI = ADF.querySelector("#album");
        const TNU = ADF.querySelector(".track_num");
        const SNG = ADF.querySelector(".song");
        const LCP = ADF.querySelector("#song_copy");
        
        const BNS = document.querySelector("#add_new");
    
        ART.addEventListener("input", async (e) => {
            QS.artist = ART.value;
            QS.id_artist=false;
            await gsa(encodeURIComponent(JSON.stringify(QS)));
            if(SN.add.id_artist==true){
                (BBA.classList.contains("vis-none"))?'':BBA.classList.add("vis-none");
                (LBM.classList.contains("vis-none"))?LBM.classList.remove("vis-none"):'';
                SR1.innerHTML = svg_tru;
                CRTALS();
            }else{
                (BBA.classList.contains("vis-none"))?BBA.classList.remove("vis-none"):'';
                (LBM.classList.contains("vis-none"))?'':LBM.classList.add("vis-none");
                (SNG.classList.contains("vis-none"))?'':SNG.classList.add("vis-none");
                SR1.innerHTML = '';
            }

            
            //ART.disabled = "disabled";
        }, false);

        BNS.addEventListener("click", (e) => {
            for (const prop of Object.getOwnPropertyNames(SN.add)) {
              delete SN.add[prop];
            }
            CRAD();
        }, false);
        
        BBA.addEventListener("click", (e) => {
            AAT.classList.remove("vis-none");
            BBA.classList.add("vis-none");
            QS.add_artist = true;
        }, false);
        
        BLA.addEventListener("click", (e) => {
            DLA.classList.remove("vis-none");
            BLA.classList.add("vis-none");
            
        }, false);  
        
        SUL.addEventListener("click", (e) => {
            QS.add_album = true;
        }, false);
 
        LCS.addEventListener("change", (e) => {
            (LCS.checked == true)?(SNG.classList.remove("vis-none"),QS.album='0',LBI.value=''):SNG.classList.add("vis-none");
            (LCS.checked == true && BLA.classList.contains("vis-none"))?'':BLA.classList.add("vis-none");
            (LCS.checked == true && DLA.classList.contains("vis-none"))?'':DLA.classList.add("vis-none");
        }, false);
    
        LBI.addEventListener("input", async (e) => {
            LCS.checked = false;
            (SNG.classList.contains("vis-none"))?'':SNG.classList.add("vis-none");
            QS.album = LBI.value;
            await gsa(encodeURIComponent(JSON.stringify(QS)));
            if(SN.add.id_album==true){
                (BLA.classList.contains("vis-none"))?'':BLA.classList.add("vis-none");
                (DLA.classList.contains("vis-none"))?'':DLA.classList.add("vis-none");
                (TNU.classList.contains("vis-none"))?TNU.classList.remove("vis-none"):'';
                (SNG.classList.contains("vis-none"))?SNG.classList.remove("vis-none"):'';
                //SR1.innerHTML = svg_tru;
                CRTALS();
            }else{
                (BLA.classList.contains("vis-none"))?BLA.classList.remove("vis-none"):'';
                (DLA.classList.contains("vis-none"))?'':BLA.classList.add("vis-none");
                (TNU.classList.contains("vis-none"))?'':TNU.classList.add("vis-none");
                (SNG.classList.contains("vis-none"))?'':SNG.classList.add("vis-none");
                //SR1.innerHTML = '';
            }
        }, false);
        
        const STA = ADF.querySelector("#artist_thumb");
        STA.addEventListener("focusout", (e) => {
            const IMS = ADF.querySelector("#img_artist_thumb");
            IMS.src = (STA.value)?STA.value:'/thumb/remove-image.svg';
        }, false);
        
        const STL = ADF.querySelector("#album_thumb");
        STL.addEventListener("focusout", (e) => {
            const IMS = ADF.querySelector("#img_album_thumb");
            IMS.src = (STL.value)?STL.value:'/thumb/remove-image.svg';
        }, false);
        
        const STB = ADF.querySelector("#song_thumb");
        STB.addEventListener("focusout", (e) => {
            const IMS = ADF.querySelector("#img_song_thumb");
            IMS.src = (STB.value)?STB.value:'/thumb/remove-image.svg';
        }, false);

        const SFL = ADF.querySelector("#song_file");
        SFL.addEventListener("focusout", (e) => {
            const AUD = ADF.querySelector("#song_audio");
            (SFL.value)?(AUD.src = SFL.value,
            AUD.controls = 'controls'):
            (AUD.src = '',AUD.controls = '');
        }, false);
    
        ADF.addEventListener("submit",  (e) => { e.preventDefault();GET_IN(ADF); }, false);
    
        async function GET_IN(f) {
            
            let ari = f.querySelectorAll("input");
            let asi = f.querySelectorAll("select");
            let abs = f.querySelectorAll("button");
            
            await abs.forEach((e) =>{
                e.classList.add("vis-none");
            });
            await ari.forEach((e) =>{
                QS[e.id] = e.value;
            });
            await asi.forEach((e) =>{
                QS[e.id] = e.value;
            });
            QS['single'] = (LCS.checked == true)?true:false;
            QS['song_copy'] = (LCP.checked == true)?true:false;
            
            console.log(QS);
            ADF.append(spn_lrg);
            await gsa(encodeURIComponent(JSON.stringify(QS)));
            if(SN.add.ok==true){
                await abs.forEach((e) =>{
                    e.classList.remove("vis-none");
                });
                CRTM(SN.add.err);
                ADF.removeChild(spn_lrg);
            }
        }

        function CRTALS(l){
            if(SN.add.album_list){
                LBI.setAttribute('list', 'albumlist');
                SN.add.album_list.forEach((e) =>{
                    const opl = OA(document.createElement("option"), {className: "" ,value:e.name });
                    DTL.append(opl);
                });
            }
        }

        if(SN.add.id_artist==true){
                (BBA.classList.contains("vis-none"))?'':BBA.classList.add("vis-none");
                (LBM.classList.contains("vis-none"))?LBM.classList.remove("vis-none"):'';
                SR1.innerHTML = svg_tru;
                CRTALS();
        }        

        if(SN.add.single=='on'){
            SNG.classList.remove("vis-none");
            LCS.checked = true;
        }
        
        if(SN.add.add==true){
            ADF.classList.add("vis-none");
            CAS.classList.remove("vis-none");
        }
        

    }

    function CSH(title, href) {
    
        var hed = OA(document.createElement("div"), {
            className: "section-header m-top-3"
        });
    
        var ht = OA(document.createElement("h2"), {
            className: "title",
            innerHTML: title
        });
    
        hed.appendChild(ht);
    
        if (href) {
    
            var ha = OA(document.createElement("a"), {
                className: "see-all",
                innerHTML: "See All",
                href: href
            });
    
            hed.appendChild(ha);
        }
    
        MCNT.appendChild(hed);
    
        return hed;
    }
    
    /**
     * CRPL — ساخت UI شل پلیر (#player-box)
     * media elements (vply/aply) در HTML می‌مانند؛ chrome از اینجا ساخته می‌شود.
     */
    function CRPL() {
        const playerRoot = document.getElementById('player');
        const box = document.getElementById('player-box');
        if (!playerRoot || !box) return;
        if (playerRoot._crplBuilt) return;
        playerRoot._crplBuilt = true;

        // media-stage (audio / video-without-picture)
        let stage = document.getElementById('media-stage');
        if (!stage) {
            stage = OA(document.createElement('div'), {
                id: 'media-stage',
                className: 'media-stage detailes-container vis-none'
            });
            const stageBg = OA(document.createElement('div'), { id: 'stage-bg', className: 'stage-bg' });
            const det = OA(document.createElement('div'), { id: 'detailes-container' });
            const texts = OA(document.createElement('div'), { className: 'texts media-stage-title-wrap' });
            texts.append(OA(document.createElement('h1'), {
                id: 'media-stage-title',
                className: 'text1'
            }));
            const hdr = OA(document.createElement('div'), {
                id: 'header-container',
                className: 'page-detail-header-container media-stage-header'
            });
            const thumbWrap = OA(document.createElement('div'), { className: 'thumbail' });
            const stageThumb = OA(document.createElement('img'), {
                id: 'media-stage-thumb',
                className: 'ratio ratio-1x1 vis-none',
                alt: ''
            });
            thumbWrap.append(stageThumb);
            
            const artistBox = OA(document.createElement('div'), {
                id: 'media-stage-artists',
                className: 'media-stage-artists'
            });
            const aboutBox = OA(document.createElement('div'), {
                id: 'media-stage-about',
                className: 'media-stage-about'
            });
            hdr.append(thumbWrap, artistBox, aboutBox);
            det.append(texts, hdr);
            const plBox = OA(document.createElement('div'), {
                id: 'media-stage-lyric',
                className: 'playlist-container media-stage-lyric ha-lyric-skin'
            });
            plBox.setAttribute('data-ha-lyric-skin', '1');
            stage.append(stageBg, det, plBox);
            playerRoot.insertBefore(stage, box);
        }

        box.innerHTML = '';

        const backdrop = OA(document.createElement('div'), { className: 'backdrop', id: 'backdrop' });

        // head
        const head = OA(document.createElement('div'), { className: 'player-head' });
        const headLeft = OA(document.createElement('div'), { className: 'head-left' });
        const backBtn = OA(document.createElement('div'), {
            className: 'back btn',
            id: 'BackToHome',
            title: 'Back',
            innerHTML: '<span>' + svg_bak + '</span>'
        });
        headLeft.append(backBtn);
        head.append(headLeft);

        const floatBtn = OA(document.createElement('div'), { id: 'float-btn', className: 'float-btn' });
        floatBtn.style.display = 'none';
        const nextFloat = OA(document.createElement('div'), { id: 'next-btn', className: 'float-btn next-float-btn' });
        nextFloat.style.display = 'none';

        // progress
        const progress = OA(document.createElement('div'), { className: 'player-progress' });
        progress.append(
            OA(document.createElement('span'), { className: 'current-time', innerHTML: '00:00' }),
            OA(document.createElement('div'), {
                className: 'seekbar',
                id: 'main-progress',
                innerHTML: '<input type="range" class="form-range" min="0" max="100" step="0.1">'
            }),
            OA(document.createElement('span'), { className: 'duration', innerHTML: '00:00' })
        );

        // footer
        const footer = OA(document.createElement('div'), { className: 'player-footer' });

        // left settings: queue / subtitle / lyric
        const setLeft = OA(document.createElement('div'), { className: 'player-setting' });
        const btnStg = OA(document.createElement('div'), {
            id: 'player-settings-btn', className: 'btn', title: 'Settings', innerHTML: svg_stg
        });
        const btnQueue = OA(document.createElement('div'), {
            id: 'playlist', className: 'g-30 btn', title: 'Queue', innerHTML: svg_pls
        });
        const btnSub = OA(document.createElement('div'), {
            id: 'subtitle', className: 'g-31 btn vis-none', title: 'Subtitle', innerHTML: svg_sub
        });
        const btnLyr = OA(document.createElement('div'), {
            id: 'lyric', className: 'g-31 btn vis-none', title: 'Lyrics', innerHTML: svg_lrc
        });
        setLeft.append(btnStg,btnQueue, btnSub, btnLyr);

        // center controls
        const control = OA(document.createElement('div'), { className: 'player-control' });
        const btnPrev = OA(document.createElement('div'), {
            id: 'prev', className: 'btn play-prev', title: 'Previous', innerHTML: svg_prv
        });
        const btnBw10 = OA(document.createElement('div'), {
            id: 'backward-10', className: 'btn', title: '-10s', innerHTML: svg_bw10
        });
        const btnPlay = OA(document.createElement('div'), {
            id: 'play', className: 'play-pause btn', title: 'Play/Pause',
            innerHTML: svg_plys + '<i class="wlap"></i>'
        });
        const btnFw10 = OA(document.createElement('div'), {
            id: 'forward-10', className: 'btn', title: '+10s', innerHTML: svg_fw10
        });
        const btnNext = OA(document.createElement('div'), {
            id: 'next', className: 'btn play-next', title: 'Next', innerHTML: svg_nxt
        });
        control.append(btnPrev, btnBw10, btnPlay, btnFw10, btnNext);

        // right: repeat / shuffle / sources / settings
        const setRight = OA(document.createElement('div'), { className: 'player-setting' });
        const btnRep = OA(document.createElement('div'), {
            id: 'repeat', className: 'repeat btn', title: 'Repeat', innerHTML: svg_rep
        });
        const btnShuf = OA(document.createElement('div'), {
            id: 'shuffle', className: 'btn btn-shuffle', title: 'Shuffle', innerHTML: svg_shfo
        });
        const btnSrc = OA(document.createElement('div'), {
            id: 'player-sources-btn', className: 'btn', title: 'Sources', innerHTML: svg_dsk
        });
        setRight.append(btnRep, btnShuf, btnSrc);

        footer.append(setLeft, control, setRight);

        const timer = OA(document.createElement('div'), { id: 'timer' });

        // in-shell panel (settings / sources)
        const panel = OA(document.createElement('div'), {
            id: 'player-panel',
            className: 'player-panel'
        });
        const panelHead = OA(document.createElement('div'), { className: 'player-panel-head' });
        panelHead.append(
            OA(document.createElement('div'), {
                id: 'player-panel-title',
                className: 'title',
                innerHTML: 'Player settings'
            })
        );
        const panelClose = OA(document.createElement('div'), {
            className: 'close btn',
            title: 'Close',
            innerHTML: svg_bak
        });
        panelHead.append(panelClose);
        const panelTabs = OA(document.createElement('div'), { className: 'player-panel-tabs' });
        const tabSet = OA(document.createElement('div'), {
            className: 'tab active',
            innerHTML: 'Settings'
        });
        tabSet.setAttribute('data-mode', 'settings');
        const tabQue = OA(document.createElement('div'), {
            className: 'tab',
            innerHTML: 'Playlist'
        });
        tabQue.setAttribute('data-mode', 'queue');
        const tabSrc = OA(document.createElement('div'), {
            className: 'tab',
            innerHTML: 'Sources'
        });
        tabSrc.setAttribute('data-mode', 'sources');
        panelTabs.append(tabSet, tabQue, tabSrc);
        const panelBody = OA(document.createElement('div'), { id: 'player-panel-body' });
        panel.append(panelHead, panelTabs, panelBody);

        box.append(backdrop, head, floatBtn, nextFloat, progress, footer, timer, panel);

        // transport اصلی (play/prev/next/repeat/shuffle) فقط در InitMediaPlayers با wireOnce
        // جلوگیری از double-fire روی play/pause
        btnBw10.addEventListener('click', () => seekRelative(-10));
        btnFw10.addEventListener('click', () => seekRelative(10));
        btnStg.addEventListener('click', () => openPlayerSettings());
        btnSrc.addEventListener('click', () => openPlayerSources());
        panelClose.addEventListener('click', () => closePlayerPanel());
        tabSet.addEventListener('click', () => openPlayerPanel('settings'));
        tabQue.addEventListener('click', () => openPlayerPanel('queue'));
        tabSrc.addEventListener('click', () => openPlayerPanel('sources'));
        btnQueue.addEventListener('click', () => openPlayerPanel('queue'));
        btnLyr.addEventListener('click', () => {
            const stageLyr = document.getElementById('media-stage-lyric');
            if (!stageLyr) return;
            const open = stageLyr.dataset.open === '1';
            if (open) {
                stageLyr.classList.add('is-hidden');
                stageLyr.dataset.open = '0';
                btnLyr.classList.remove('active');
            } else {
                stageLyr.classList.remove('is-hidden');
                stageLyr.dataset.open = '1';
                btnLyr.classList.add('active');
                // اگر هنوز mount نشده
                const P = ensurePlayer();
                const item = P.nowPlaying && P.nowPlaying.item;
                if (item) LyricsEngine.mountDefaults(item);
                LyricsEngine.sync((GetActiveMediaElement() && GetActiveMediaElement().currentTime) || 0);
            }
            revealPlayerControls();
        });

        // controls auto-hide / reveal — روی کل #player (ویدیو + media-stage + شل)
        if (!playerRoot._haControlsReveal) {
            playerRoot._haControlsReveal = true;
            const onReveal = (ev) => {
                if (ev && ev.target && ev.target.closest && ev.target.closest('#player-panel')) return;
                revealPlayerControls();
            };
            playerRoot.addEventListener('click', onReveal);
            playerRoot.addEventListener('mousemove', onReveal);
            playerRoot.addEventListener('touchstart', onReveal, { passive: true });
        }

        // apply saved UI prefs
        applyMediaStageSpin(!!GetSetting('rotatingHeader'));
        applyLyricSkin();
        wireMaxLyricsTools();
        armPlayerControlsHide();
    }

    function seekRelative(sec) {
        const media = GetActiveMediaElement();
        if (!media || !Number.isFinite(media.duration)) return;
        let t = (media.currentTime || 0) + sec;
        if (t < 0) t = 0;
        if (t > media.duration) t = media.duration;
        try { media.currentTime = t; } catch (e) {}
    }

    function openPlayerSettings() {
        openPlayerPanel('settings');
    }

    function openPlayerSources() {
        openPlayerPanel('sources');
    }

    function closePlayerPanel() {
        const panel = document.getElementById('player-panel');
        if (panel) panel.classList.remove('is-open');
        const P = ensurePlayer();
        P._playerPanelMode = null;
        armPlayerControlsHide();
    }

    function openPlayerPanel(mode) {
        const panel = document.getElementById('player-panel');
        const body = document.getElementById('player-panel-body');
        const title = document.getElementById('player-panel-title');
        if (!panel || !body) return;
        const P = ensurePlayer();
        P._playerPanelMode = mode || 'settings';
        // وقتی پنل باز است کنترلر محو نشود
        const box = document.getElementById('player-box');
        if (box) box.classList.remove('controls-hidden');
        if (P._controlsHideTimer) {
            clearTimeout(P._controlsHideTimer);
            P._controlsHideTimer = null;
        }
        // tabs active
        panel.querySelectorAll('.player-panel-tabs .tab').forEach((t) => {
            t.classList.toggle('active', t.getAttribute('data-mode') === mode);
        });
        body.innerHTML = '';
        if (mode === 'sources') {
            if (title) title.innerHTML = 'Sources';
            renderPlayerSourcesPanel(body);
        } else if (mode === 'queue') {
            const n = (P.queue || []).length;
            if (title) title.innerHTML = 'Next Playing (' + n + ')';
            renderPlayerQueuePanel(body);
        } else {
            if (title) title.innerHTML = 'Player settings';
            renderPlayerSettingsPanel(body);
        }
        panel.classList.add('is-open');
    }

    function openPlayerQueue() {
        openPlayerPanel('queue');
    }

    function renderPlayerQueuePanel(body) {
        const list = OA(document.createElement('div'), { className: 'player-queue-list' });
        body.append(list);
        renderQueueItems(list, { compact: true });
    }

    function makeToggleRow(label, key, onChange) {
        const row = OA(document.createElement('div'), { className: 'player-toggle-row' });
        row.append(OA(document.createElement('div'), { className: 'label', innerHTML: label }));
        const on = !!GetSetting(key);
        const tog = OA(document.createElement('div'), {
            className: 'player-toggle' + (on ? ' on' : ''),
            innerHTML: '<span></span>'
        });
        tog.addEventListener('click', () => {
            const next = !tog.classList.contains('on');
            tog.classList.toggle('on', next);
            SetSetting(key, next);
            if (typeof onChange === 'function') onChange(next);
        });
        row.append(tog);
        return row;
    }

    function renderPlayerSettingsPanel(body) {
        // ---- UI ----
        const secUi = OA(document.createElement('div'), { className: 'player-panel-section' });
        secUi.append(OA(document.createElement('div'), { className: 'sec-title', innerHTML: 'Interface' }));
        secUi.append(makeToggleRow('Spinning header', 'rotatingHeader', (on) => {
            applyMediaStageSpin(on);
        }));
        secUi.append(makeToggleRow('Keep controls visible', 'keepControllerVisible', (on) => {
            if (on) {
                const box = document.getElementById('player-box');
                if (box) box.classList.remove('controls-hidden');
            } else {
                armPlayerControlsHide();
            }
        }));
        body.append(secUi);

        // ---- Quality (from actual playable sources) ----
        const secQ = OA(document.createElement('div'), { className: 'player-panel-section' });
        secQ.append(OA(document.createElement('div'), { className: 'sec-title', innerHTML: 'Quality' }));
        const qList = OA(document.createElement('div'), { className: 'player-quality-list' });
        const P = ensurePlayer();
        const item = P.nowPlaying && P.nowPlaying.item;
        const isVideo = P.nowPlaying && P.nowPlaying.player === 'video';
        const sources = getPlayableSources(item);
        // unique bits desc
        const bits = [];
        const seen = new Set();
        sources.forEach((s) => {
            const b = sourceBit(s);
            if (!seen.has(b)) { seen.add(b); bits.push(b); }
        });
        bits.sort((a, b) => b - a);
        const prefKey = isVideo ? 'videoQuality' : 'audioQuality';
        // map pref high/medium/low to nearest available
        const currentIdx = P.nowPlaying ? (P.nowPlaying.sourceIndex || 0) : 0;
        const currentBit = sources[currentIdx] ? sourceBit(sources[currentIdx]) : null;
        if (!bits.length) {
            qList.append(OA(document.createElement('div'), {
                className: 'text2',
                innerHTML: 'No playable sources'
            }));
        } else {
            bits.forEach((bit) => {
                const row = OA(document.createElement('div'), {
                    className: 'player-quality-item' + (currentBit === bit ? ' active' : '')
                });
                row.append(OA(document.createElement('span'), {
                    className: bitBadgeClass(bit, isVideo),
                    innerHTML: formatBitLabel(bit, isVideo)
                }));
                row.addEventListener('click', () => {
                    // pick first playable source with this bit
                    const idx = sources.findIndex((s) => sourceBit(s) === bit);
                    if (idx < 0) return;
                    // store soft preference
                    if (bits.length) {
                        const maxB = bits[0];
                        const minB = bits[bits.length - 1];
                        let pref = 'medium';
                        if (bit === maxB) pref = 'high';
                        else if (bit === minB) pref = 'low';
                        SetSetting(prefKey, pref);
                    }
                    PlayMediaSourceAt(idx, { preserveTime: true });
                    openPlayerPanel('settings'); // refresh active
                });
                qList.append(row);
            });
        }
        secQ.append(qList);
        body.append(secQ);

        // ---- Lyric font size (پیوسته) + presets + family ----
        const secL = OA(document.createElement('div'), { className: 'player-panel-section' });
        secL.append(OA(document.createElement('div'), { className: 'sec-title', innerHTML: 'Lyrics size' }));
        const fsWrap = OA(document.createElement('div'), { className: 'lyric-size-slider', style: 'max-width:100%' });
        const curPx = getLyricFontPx();
        const range = OA(document.createElement('input'), {
            type: 'range',
            id: 'player-lyric-size',
            min: String(LYRIC_SIZE_MIN),
            max: String(LYRIC_SIZE_MAX),
            step: '1',
            value: String(curPx)
        });
        range.addEventListener('input', () => setLyricFontPx(range.value));
        fsWrap.append(
            OA(document.createElement('span'), { className: 'lz-lab', innerHTML: 'S' }),
            range,
            OA(document.createElement('span'), { className: 'lz-lab', innerHTML: 'XL' })
        );
        secL.append(fsWrap);
        // S M L XL — کلیک = پرش دقیق اسلایدر به همان سایز
        const presetWrap = OA(document.createElement('div'), {
            className: 'player-font-size lyric-size-presets',
            style: 'margin-top:10px'
        });
        const activePreset = lyricPresetForPx(curPx);
        [
            { v: 'sm', l: 'S' },
            { v: 'md', l: 'M' },
            { v: 'lg', l: 'L' },
            { v: 'xl', l: 'XL' }
        ].forEach((opt) => {
            const b = OA(document.createElement('div'), {
                className: 'fs-btn' + (activePreset === opt.v ? ' active' : ''),
                innerHTML: opt.l
            });
            b.setAttribute('data-size', opt.v);
            b.addEventListener('click', () => {
                const px = LYRIC_SIZE_PRESETS[opt.v] || 16;
                setLyricFontPx(px, opt.v);
            });
            presetWrap.append(b);
        });
        secL.append(presetWrap);
        const ffWrap = OA(document.createElement('div'), { className: 'lyric-font-keys', style: 'margin-top:10px' });
        const curFf = GetSetting('lyricFontFamily') || '';
        ['f1', 'f2', 'f3', 'f4'].forEach((k) => {
            const b = OA(document.createElement('div'), {
                className: 'fs-btn' + (curFf === k ? ' active' : ''),
                innerHTML: k.toUpperCase()
            });
            b.setAttribute('data-font', k);
            b.addEventListener('click', () => {
                let next = k;
                if ((GetSetting('lyricFontFamily') || '') === k) next = '';
                SetSetting('lyricFontFamily', next);
                applyLyricFontFamily(next);
            });
            ffWrap.append(b);
        });
        secL.append(ffWrap);
        body.append(secL);

        // ---- Lyrics display mode ----
        const secLM = OA(document.createElement('div'), { className: 'player-panel-section' });
        secLM.append(OA(document.createElement('div'), { className: 'sec-title', innerHTML: 'Lyrics style' }));
        const lmWrap = OA(document.createElement('div'), { className: 'player-font-size' });
        const curLm = GetSetting('lyricDisplayMode') || 'normal';
        [
            { v: 'normal', l: 'نرمال' },
            { v: 'line', l: 'خطی' },
            { v: 'focus', l: 'فوکوس' },
            { v: 'card', l: 'کارت' }
        ].forEach((opt) => {
            const b = OA(document.createElement('div'), {
                className: 'fs-btn' + (curLm === opt.v ? ' active' : ''),
                innerHTML: opt.l
            });
            b.addEventListener('click', () => {
                SetSetting('lyricDisplayMode', opt.v);
                try { LyricsEngine.setDisplayMode(opt.v); } catch (e) {}
                openPlayerPanel('settings');
            });
            lmWrap.append(b);
        });
        secLM.append(lmWrap);
        body.append(secLM);

        // ---- More (placeholder) ----
        const secM = OA(document.createElement('div'), { className: 'player-panel-section' });
        secM.append(OA(document.createElement('div'), { className: 'sec-title', innerHTML: 'More' }));
        secM.append(makeToggleRow('Crossfade (soon)', 'crossfade', null));
        const sleepRow = OA(document.createElement('div'), { className: 'player-toggle-row' });
        sleepRow.append(OA(document.createElement('div'), { className: 'label', innerHTML: 'Sleep timer' }));
        const sleepVal = Number(GetSetting('sleepTimer') || 0);
        const sleepLabel = OA(document.createElement('div'), {
            className: 'text2',
            innerHTML: sleepVal ? (sleepVal + ' min') : 'Off'
        });
        sleepRow.addEventListener('click', () => {
            const opts = [0, 15, 30, 45, 60];
            const i = opts.indexOf(sleepVal);
            const next = opts[(i + 1) % opts.length];
            SetSetting('sleepTimer', next);
            sleepLabel.innerHTML = next ? (next + ' min') : 'Off';
            // actual timer wiring later
        });
        sleepRow.append(sleepLabel);
        secM.append(sleepRow);
        body.append(secM);
    }

    function renderPlayerSourcesPanel(body) {
        const P = ensurePlayer();
        const item = P.nowPlaying && P.nowPlaying.item;
        const isVideo = P.nowPlaying && P.nowPlaying.player === 'video';
        // همه سورس‌های قابل پخش (بدون demo)
        const sources = getPlayableSources(item);
        const list = OA(document.createElement('div'), { className: 'player-source-list' });
        if (!sources.length) {
            list.append(OA(document.createElement('div'), {
                className: 'text2',
                innerHTML: 'No sources'
            }));
            body.append(list);
            return;
        }
        const curIdx = P.nowPlaying ? (P.nowPlaying.sourceIndex || 0) : -1;
        sources.forEach((src, i) => {
            const row = OA(document.createElement('div'), {
                className: 'player-source-row' + (i === curIdx ? ' active' : '')
            });
            // شماره یا eq
            const num = OA(document.createElement('div'), { className: 'ps-num' });
            if (i === curIdx) {
                num.innerHTML = svg_eql;
            } else {
                num.innerHTML = String(i + 1);
            }
            row.append(num);

            // تامب owner
            const oid = sourceOwnerId(src);
            let owner = (src.owner && typeof src.owner === 'object') ? src.owner : null;
            if (!owner || !owner.name) owner = getOwner(oid) || owner;
            const thumbBox = OA(document.createElement('div'), { className: 'ps-thumb' });
            const tmb = owner && owner.meta && owner.meta.tmb ? owner.meta.tmb : null;
            if (tmb) {
                thumbBox.append(OA(document.createElement('img'), {
                    src: UT + tmb + '.jpg',
                    alt: ''
                }));
            } else {
                thumbBox.append(OA(document.createElement('span'), {
                    className: 'placeholder rounded-circle',
                    innerHTML: svg_dons
                }));
            }
            row.append(thumbBox);

            let labelName = '';
            if (oid !== 0 && owner && owner.name) labelName = String(owner.name).trim();
            if (!labelName) labelName = hostLabelFromUrl(src.url) || ('Source ' + (i + 1));
            const meta = OA(document.createElement('div'), {
                className: 'ps-meta',
                innerHTML: labelName
            });
            row.append(meta);

            const bit = sourceBit(src);
            row.append(OA(document.createElement('span'), {
                className: bitBadgeClass(bit, isVideo),
                innerHTML: formatBitLabel(bit, isVideo)
            }));

            row.addEventListener('click', () => {
                if (i === curIdx) return;
                PlayMediaSourceAt(i, { preserveTime: true });
                openPlayerPanel('sources');
            });
            list.append(row);
        });
        body.append(list);
    }

    function applyMediaStageSpin(on) {
        const thumbWrap = document.querySelector('#media-stage .thumbail');
        if (!thumbWrap) return;
        if (on) {
            thumbWrap.classList.add('player-art-spin');
            const P = ensurePlayer();
            if (P.nowPlaying && P.nowPlaying.isPlaying) {
                thumbWrap.classList.add('is-playing');
            }
        } else {
            thumbWrap.classList.remove('player-art-spin', 'is-playing');
        }
    }

    const LYRIC_SIZE_MIN = 12;
    const LYRIC_SIZE_MAX = 32;
    const LYRIC_SIZE_PRESETS = { sm: 13, md: 16, lg: 20, xl: 24 };
    const LYRIC_FONT_MAP = {
        f1: '"Vazirmatn", "Tahoma", "Segoe UI", sans-serif',
        f2: 'Georgia, "Times New Roman", serif',
        f3: '"Segoe UI", Roboto, "Helvetica Neue", sans-serif',
        f4: '"Courier New", Consolas, monospace'
    };

    function clampLyricPx(n) {
        n = Math.round(Number(n));
        if (!Number.isFinite(n)) n = 16;
        return Math.max(LYRIC_SIZE_MIN, Math.min(LYRIC_SIZE_MAX, n));
    }

    function getLyricFontPx() {
        const raw = GetSetting('lyricFontSizePx');
        if (raw != null && raw !== '' && Number.isFinite(Number(raw))) {
            return clampLyricPx(raw);
        }
        // مهاجرت از sm/md/lg/xl
        const key = GetSetting('lyricFontSize') || 'md';
        return clampLyricPx(LYRIC_SIZE_PRESETS[key] || 16);
    }

    /** نزدیک‌ترین رنج دکمه‌ای برای هایلایت فعال */
    function lyricPresetForPx(px) {
        px = clampLyricPx(px);
        const mids = [
            (LYRIC_SIZE_PRESETS.sm + LYRIC_SIZE_PRESETS.md) / 2,
            (LYRIC_SIZE_PRESETS.md + LYRIC_SIZE_PRESETS.lg) / 2,
            (LYRIC_SIZE_PRESETS.lg + LYRIC_SIZE_PRESETS.xl) / 2
        ];
        if (px < mids[0]) return 'sm';
        if (px < mids[1]) return 'md';
        if (px < mids[2]) return 'lg';
        return 'xl';
    }

    function syncLyricSizeControls(px) {
        px = clampLyricPx(px);
        const preset = lyricPresetForPx(px);
        document.querySelectorAll('#max-lyrics-size, #player-lyric-size').forEach((el) => {
            el.value = String(px);
        });
        document.querySelectorAll('.lyric-size-presets .fs-btn').forEach((b) => {
            const k = b.getAttribute('data-size') || '';
            b.classList.toggle('active', k === preset);
        });
    }

    function applyLyricFontSize(pxOrKey) {
        let px;
        if (typeof pxOrKey === 'string' && LYRIC_SIZE_PRESETS[pxOrKey] != null) {
            px = LYRIC_SIZE_PRESETS[pxOrKey];
        } else if (pxOrKey == null || pxOrKey === '') {
            px = getLyricFontPx();
        } else {
            px = clampLyricPx(pxOrKey);
        }
        const css = px + 'px';
        // فقط متغیر سراسری — باکس با --ha-lyric-box-size سایز خودش را نگه می‌دارد
        document.documentElement.style.setProperty('--ha-lyric-size', css);
        document.querySelectorAll('.ha-lyric-skin, [data-ha-lyric-skin]').forEach((el) => {
            // اگر باکس سایز اختصاصی دارد، inline ننویس
            if (el.style.getPropertyValue('--ha-lyric-box-size') || el.classList.contains('mini-lyric')) {
                el.style.fontSize = '';
                return;
            }
            el.style.fontSize = '';
        });
        syncLyricSizeControls(px);
    }

    function setLyricFontPx(px, fromPresetKey) {
        px = clampLyricPx(px);
        SetSetting('lyricFontSizePx', px);
        SetSetting('lyricFontSize', fromPresetKey || lyricPresetForPx(px));
        applyLyricFontSize(px);
    }

    function applyLyricFontFamily(key) {
        key = key === undefined ? (GetSetting('lyricFontFamily') || '') : key;
        const stack = LYRIC_FONT_MAP[key] || '';
        if (stack) {
            document.documentElement.style.setProperty('--ha-lyric-font', stack);
        } else {
            document.documentElement.style.removeProperty('--ha-lyric-font');
        }
        document.querySelectorAll('.ha-lyric-skin, [data-ha-lyric-skin]').forEach((el) => {
            if (stack) el.style.fontFamily = stack;
            else el.style.fontFamily = '';
        });
        document.querySelectorAll('.lyric-font-keys .fs-btn').forEach((b) => {
            const k = b.getAttribute('data-font') || '';
            b.classList.toggle('active', !!key && k === key);
        });
    }

    function applyLyricSkin() {
        applyLyricFontSize(getLyricFontPx());
        applyLyricFontFamily(GetSetting('lyricFontFamily') || '');
    }

    function wireMaxLyricsTools() {
        const slider = document.getElementById('max-lyrics-size');
        if (slider && !slider._haWired) {
            slider._haWired = true;
            slider.min = String(LYRIC_SIZE_MIN);
            slider.max = String(LYRIC_SIZE_MAX);
            slider.step = '1';
            slider.value = String(getLyricFontPx());
            const onSize = () => setLyricFontPx(slider.value);
            slider.addEventListener('input', onSize);
            slider.addEventListener('change', onSize);
        }
        const keys = document.getElementById('max-lyrics-fonts');
        if (keys && !keys._haWired) {
            keys._haWired = true;
            keys.addEventListener('click', (ev) => {
                const btn = ev.target && ev.target.closest ? ev.target.closest('.fs-btn') : null;
                if (!btn || !keys.contains(btn)) return;
                let key = btn.getAttribute('data-font') || '';
                const cur = GetSetting('lyricFontFamily') || '';
                if (cur === key) key = '';
                SetSetting('lyricFontFamily', key);
                applyLyricFontFamily(key);
            });
        }
        applyLyricSkin();
    }

    function armPlayerControlsHide() {
        const P = ensurePlayer();
        if (P._controlsHideTimer) {
            clearTimeout(P._controlsHideTimer);
            P._controlsHideTimer = null;
        }
        const box = document.getElementById('player-box');
        if (!box || box.classList.contains('vis-none')) return;
        if (GetSetting('keepControllerVisible')) {
            box.classList.remove('controls-hidden');
            return;
        }
        // اگر پنل باز است محو نکن
        if (P._playerPanelMode) return;
        P._controlsHideTimer = setTimeout(() => {
            if (GetSetting('keepControllerVisible')) return;
            if (P._playerPanelMode) return;
            box.classList.add('controls-hidden');
        }, 4000);
    }

    function revealPlayerControls() {
        const box = document.getElementById('player-box');
        if (box) box.classList.remove('controls-hidden');
        armPlayerControlsHide();
    }


    function CRSC(ar, T, ART) {
    
        const sec = OA(document.createElement("section"), {className: "section-carousel-container m-top-1"});
        const isVideoish = (T === 'musicvideo' || T === 'video');
        const sec_div = OA(document.createElement("div"), {className: "carousel-container " + (isVideoish ? "video" : "")});
        ar.forEach((id) => {
            // id ممکن است object با id باشد
            if (id && typeof id === 'object' && id.id != null) id = id.id;
            
            let e = null;
            let url = HS;
            let TPS = svg_sng;
            
            if (T == 'song') {e = SN.song ? SN.song[id] : null; url = HS; }
            if (T == 'album') {e = SN.album ? SN.album[id] : null; url = HL; }
            if (T == 'video') {e = SN.video ? SN.video[id] : null; url = HV; }
            if (T == 'artist') {e = SN.artist ? SN.artist[id] : null; url = HA; }
            if (T == 'podcast') {e = SN.podcast ? SN.podcast[id] : null; url = HS; }
            if (T == 'playlist') {e = SN.playlist ? SN.playlist[id] : null; url = HPL; TPS = svg_pls; }
            if (T == 'musicvideo') {e = SN.musicvideo ? SN.musicvideo[id] : null; url = HV; }

            if (!e) {console.log("CRSC: " + T + " not found:", id);return;}

            let  artistList = [];
            let  primaryArtistUrl = "";
    
            if (ART && SN.artist && SN.artist[ART]) {
                artistList.push(SN.artist[ART]);
                primaryArtistUrl = SN.artist[ART].url || "";
            } else {
                
                const artistRefs = (e.artists && Array.isArray(e.artists)) ? e.artists : [];
                artistRefs.forEach((artistRef) => {

                    const artistId =
                        typeof artistRef === "object"
                            ? artistRef.id
                            : artistRef;
        
                    if (SN.artist && SN.artist[artistId]) {
                        artistList.push(SN.artist[artistId]);
                    }
                });
        
                if (artistList.length > 0) {
                    primaryArtistUrl = artistList[0].url || "";
                }
            }
    
            const el = OA(document.createElement("div"), {className: (T === "artist" ? "artist-box" : "track-box")});
            const elb = OA(document.createElement("div"), {className: "box"});
            elb._item = e;
            elb._type = T;
            elb.addEventListener("mouseenter", function () {
                    UHERO(this._item, this._type);
            });

            const ela = OA(document.createElement("a"), {href: url + e.id + '/' + (T !== "artist" ? primaryArtistUrl + "-" : "") + e.url});
            const art = OA(document.createElement("div"), {className: "artwork ratio " + (isVideoish ? "ratio-16x9" : "ratio-1x1")});
            const sep = OA(document.createElement("span"), {className: "placeholder " + (T === "artist" ? "rounded-circle" : ""), innerHTML: TPS });
            art.append(sep);
            
            const srcThumb = resolveThumbUrl(e, ART);
            if (srcThumb) art.append(OA(document.createElement('img'), { src: srcThumb }));

            ela.append(art);
            elb.append(ela);
            
            if(T !== "artist"){
                const mor = OA(document.createElement("div"), {className: "menu",innerHTML: svg_dot});
                mor.addEventListener("click", ()=>{CRBW(e.id,T);},false);
                
                const elp = OA(document.createElement("div"), {className: "play",innerHTML: svg_ply});
                elp.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    ev.stopPropagation();
                    if (!e) return;
            
                    if (T === 'song' || T === 'podcast' || T === 'musicvideo' || T === 'video') {
                        setPlayQueue(ar, e.id, T);
                    }
                    // album / playlist → بعداً (resolve فول + صف از آیتم‌ها)
                });
                elb.append(mor, elp);
            }
    
            const dt1 = OA(document.createElement("div"), {className: "text1"});
    
            const dta = OA(document.createElement("a"), {href: url + e.id + '/' + (T !== "artist" ? primaryArtistUrl + "-" : "") + e.url,innerHTML: e.name});
    
            dt1.append(dta);
    
            const dt2 = OA(document.createElement("div"), {className: "text2 artists-inline"});
            
            
            artistList.forEach((artistObj, j) => {
                const artL = OA(document.createElement("a"), {href: HA + artistObj.id + "/" + artistObj.url});
                const artN = OA(document.createElement("span"), {innerHTML: artistObj.name});
                artL.append(artN);
                artL.addEventListener("mouseenter", function () {
                        UHERO(artistObj, 'artist');
                });
                dt2.append(artL);
            
                if (j < artistList.length - 1) {
                    const sepSpan = OA(document.createElement("span"), {innerHTML: " - "});
                    dt2.append(sepSpan);
                }
            });
            
            el.append(elb, dt1, dt2);
            sec_div.append(el);
        });
    
        sec.append(sec_div);
    
        if (ar.length > 2) {
            CCRL(sec);
        }
    
        MCNT.append(sec);
    }

    function CRGC(ar,T,ART){

        const sec = OA(document.createElement("section"), {className: "section-container m-top-1"});
        const sec_div  = OA(document.createElement("div"), {className: "grid-container " + (T === "musicvideo" ? "musicvideo-grid-container" : "")});

        ar.forEach((id) => {
            
            let e = null;
            let url = HS;
            let TPS = svg_sng;
            
            if (T == 'song') {e = SN.song ? SN.song[id] : null; url = HS; }
            if (T == 'album') {e = SN.album ? SN.album[id] : null; url = HL; }
            if (T == 'artist') {e = SN.artist ? SN.artist[id] : null; url = HA; }
            if (T == 'playlist') {e = SN.playlist ? SN.playlist[id] : null; url = HPL; TPS = svg_pls;}
            if (T == 'musicvideo') {e = SN.musicvideo ? SN.musicvideo[id] : null; url = HV; }
            
            if (!e) {
                console.log('CRGC: ' + T + ' not found:', id);
                return; // داخل forEach = continue
            }

            let  artistList = [];
            let  primaryArtistUrl = "";
    
            if (ART && SN.artist && SN.artist[ART]) {
                artistList.push(SN.artist[ART]);
                primaryArtistUrl = SN.artist[ART].url || "";
            } else {
                
                const artistRefs = (e.artists && Array.isArray(e.artists)) ? e.artists : [];
                artistRefs.forEach((artistRef) => {

                    const artistId =
                        typeof artistRef === "object"
                            ? artistRef.id
                            : artistRef;
        
                    if (SN.artist && SN.artist[artistId]) {
                        artistList.push(SN.artist[artistId]);
                    }
                });
        
                if (artistList.length > 0) {
                    primaryArtistUrl = artistList[0].url || "";
                }
            }
            
            const el = OA(document.createElement("div"), {className: (T === "artist" ? "artist-box" : "track-box")});
            const elb = OA(document.createElement("div"), {className: "box"});
            elb._item = e;
            elb._type = T;
            elb.addEventListener("mouseenter", function () {
                    UHERO(e, T);
            });

            const ela = OA(document.createElement("a"), {href: url + e.id + '/' + (T !== "artist" ? primaryArtistUrl + "-" : "") + e.url});
            const art = OA(document.createElement("div"), {
                    className: "artwork ratio " + (T === "musicvideo" ? "ratio-16x9" : "ratio-1x1")
                });
            const sep = OA(document.createElement("span"), {className: "placeholder " + (T === "artist" ? "rounded-circle" : ""), innerHTML: TPS});
            art.append(sep);

            const srcThumb = resolveThumbUrl(e, ART);
            if (srcThumb) art.append(OA(document.createElement('img'), { src: srcThumb }));
            
            ela.append(art);
            elb.append(ela);
            if(T !== "artist"){
                const mor = OA(document.createElement("div"), {className: "menu",innerHTML: svg_dot});
                mor._item = e;
                mor._type = T;
                mor.addEventListener("click", ()=>{CRBW(e.id,T);},false);
                
                const elp = OA(document.createElement("div"), {className: "play",innerHTML: svg_ply});
                elp.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    ev.stopPropagation();
                    if (!e) return;
            
                    // فقط انواع قابل پخش
                    if (T === 'song' || T === 'podcast' || T === 'musicvideo' || T === 'video') {
                        setPlayQueue(ar, e.id, T);
                        return;
                    }
                    // آلبوم / پلی‌لیست: فعلاً برو صفحهٔ جزئیات (یا بعداً صف از آیتم‌های داخل)
                    // chp(url + e.id + '/...');
                });
    
                elb.append(mor, elp);
            }
            const dt1 = OA(document.createElement("div"), {className: "text1"});
            const dta = OA(document.createElement("a"), {href: url + e.id + '/' + (T !== "artist" ? primaryArtistUrl + "-" : "") + e.url,innerHTML: e.name});
    
            dt1.append(dta);
    
            const dt2 = OA(document.createElement("div"), {className: "text2 artists-inline"});
            
            
            artistList.forEach((artistObj, j) => {
                const artL = OA(document.createElement("a"), {href: HA + artistObj.id + "/" + artistObj.url});
                const artN = OA(document.createElement("span"), {innerHTML: artistObj.name});
                artL.append(artN);
                artL.addEventListener("mouseenter", function () {
                        UHERO(artistObj, 'artist');
                });
                dt2.append(artL);
            
                if (j < artistList.length - 1) {
                    const sepSpan = OA(document.createElement("span"), {innerHTML: " - "});
                    dt2.append(sepSpan);
                }
            });
            
            el.append(elb, dt1, dt2);
            sec_div.append(el);
        }); 

        sec.append(sec_div);
        MCNT.append(sec);
        
    }
    
    function CRAL(artists) {

        var art = OA(document.createElement("div"), {className: "artists-list m-top-1"});
        var list = (artists && Array.isArray(artists)) ? artists : [];
    
        for (var ai = 0; ai < list.length; ai++) {
    
            var artistId = (typeof list[ai] === 'object') ? list[ai].id : list[ai];
            var artist = SN.artist ? SN.artist[artistId] : null;
    
            if (!artist) {
                console.log("CRAL: Artist not found:", artistId);
                continue;
            }
    
            var artistDiv = OA(document.createElement("div"), {className: "list"});
            art.appendChild(artistDiv);
            
            var dtm = OA(document.createElement("div"), {className: "thumbail rounded-circle"});
            var artistThumb = artist.meta && artist.meta.tmb ? artist.meta.tmb : "undefined";
            var artistImg = OA(document.createElement("img"), {className: "rounded-circle", src: UT + artistThumb + ".jpg"});
            dtm.appendChild(artistImg);
            artistDiv.appendChild(dtm);
    
            var artistLink = OA(document.createElement("a"), {href: HA + artist.id + "/" + artist.url});
            var artistName = OA(document.createElement("span"), {innerHTML: artist.name});
            artistLink.appendChild(artistName);
            artistDiv.appendChild(artistLink);
        }
    
        return art;
    }

    function CRGL(ar,T,ART){

        const sec = OA(document.createElement("section"), {className: "track-line-container m-top-1"});
        
        
        ar.forEach((id) => {
            
            let e = null;
            let url = HS;
            let TPS = svg_sng;
            
            if (T == 'song') {e = SN.song ? SN.song[id] : null; url = HS; }
            if (T == 'album') {e = SN.album ? SN.album[id] : null; url = HL; }
            if (T == 'playlist') {e = SN.playlist ? SN.playlist[id] : null; url = HPL; TPS = svg_pls;}
            
            if (!e) {
                console.log('CRGC: ' + T + ' not found:', id);
                return; // داخل forEach = continue
            }

            let  artistList = [];
            let  primaryArtistUrl = "";
    
            if (ART && SN.artist && SN.artist[ART]) {
                artistList.push(SN.artist[ART]);
                primaryArtistUrl = SN.artist[ART].url || "";
            } else {
                
                const artistRefs = (e.artists && Array.isArray(e.artists)) ? e.artists : [];
                artistRefs.forEach((artistRef) => {

                    const artistId =
                        typeof artistRef === "object"
                            ? artistRef.id
                            : artistRef;
        
                    if (SN.artist && SN.artist[artistId]) {
                        artistList.push(SN.artist[artistId]);
                    }
                });
        
                if (artistList.length > 0) {
                    primaryArtistUrl = artistList[0].url || "";
                }
            }
            
            const el = OA(document.createElement("div"), {className: "track-line"});
            const elb = OA(document.createElement("div"), {className: "box"});
            elb._item = e;
            elb._type = T;
            elb.addEventListener("mouseenter", function () {
                    UHERO(this._item, this._type);
            });
            
            const ela = OA(document.createElement("a"), {href: url + e.id + '/' + primaryArtistUrl + "-" + e.url});
            const art = OA(document.createElement("div"), {className: "artwork ratio ratio-1x1"});
            const sep = OA(document.createElement("span"), {className: "placeholder", innerHTML: TPS});
            art.append(sep);
            
            const srcThumb = resolveThumbUrl(e, ART);
            if (srcThumb) art.append(OA(document.createElement('img'), { src: srcThumb }));

            ela.addEventListener("mouseenter", function () {UHERO(e, T);});
            ela.append(art);
            
            
            const dpl = OA(document.createElement("div"), {className: "play", innerHTML: svg_ply});
            dpl.addEventListener('click', (ev) => {
                ev.preventDefault();
                ev.stopPropagation();
                if (!e) return;
                if (T === 'song' || T === 'podcast' || T === 'musicvideo' || T === 'video') {
                    setPlayQueue(ar, e.id, T);
                }
            });
            const dst = OA(document.createElement("div"), {className: "texts"});
            const det = OA(document.createElement("div"), {className: "title"}); 
            const tta = OA(document.createElement("a"), {draggable: "false", href: url + e.id + '/' + primaryArtistUrl + "-" + e.url, innerHTML: e.name});
            tta.addEventListener("mouseenter", function () {UHERO(e, T);});
            det.append(tta);      

            const dt2 = OA(document.createElement("div"), {className: "text2 artist artists-inline"});
            artistList.forEach((artistObj, j) => {
                const artL = OA(document.createElement("a"), {href: HA + artistObj.id + "/" + artistObj.url});
                const artN = OA(document.createElement("span"), {innerHTML: artistObj.name});
                artL.append(artN);
                artL._item = artistObj.id;
                artL._type = 'artist';
                artL.addEventListener("mouseenter", function () {UHERO(artistObj, 'artist');});
                dt2.append(artL);
            
                if (j < artistList.length - 1) {
                    const sepSpan = OA(document.createElement("span"), {innerHTML: " - "});
                    dt2.append(sepSpan);
                }
            });
            
            const dmt = OA(document.createElement('div'), { className: 'meta' });
            dmt.append(
                OA(document.createElement('div'), {
                    className: 'date',
                    innerHTML: e.meta?.bd || e.meta?.rls || ''
                }),
                OA(document.createElement('div'), { className: 'duration' })
            );
            const dma = OA(document.createElement('div'), { className: 'more-arrow' });
            const mor = OA(document.createElement("div"), {className: "more", innerHTML: svg_dot});
            mor.addEventListener("click", ()=>{CRBW(e.id,T);},false);
            
            
            dst.append(det,dt2);
            el.append(ela,dpl,dst,dmt,dma,mor);
            sec.append(el);
        });

        MCNT.append(sec);

    }
    
    /**
     * تامب آیتم از SN (بدون شبکه)
     * ویدیو/موزیک‌ویدیو: اولویت meta.tmv سپس meta.tmb
     * @param {object} e     آیتم song/album/video/...
     * @param {string|number} [ART]  ایدی اجباری یک آرتیست (مثلاً صفحهٔ آرتیست)
     * @returns {string|null}  کلید تامب یا null
     */
    function resolveThumb(e, ART) {
        if (e && e.meta) {
            const isVideoish =
                e.meta.type === 'video' ||
                e.meta.type === 'musicvideo' ||
                e.type === 'video' ||
                e.type === 'musicvideo';
            // ویدیو: اول tmv (تامب ویدیویی)، بعد tmb
            if (isVideoish) {
                if (e.meta.tmv) return e.meta.tmv;
                if (e.meta.tmb) return e.meta.tmb;
            } else if (e.meta.tmb) {
                return e.meta.tmb;
            }
        }
    
        // آرتیست صریح
        if (ART != null && SN.artist && SN.artist[ART]) {
            const a = SN.artist[ART];
            if (a.meta && a.meta.tmb) return a.meta.tmb;
        }
    
        // لیست artists روی خود آیتم
        const refs = e && Array.isArray(e.artists) ? e.artists : [];
        for (let i = 0; i < refs.length; i++) {
            const aid = typeof refs[i] === 'object' ? refs[i].id : refs[i];
            if (aid == null || !SN.artist || !SN.artist[aid]) continue;
            const a = SN.artist[aid];
            if (a.meta && a.meta.tmb) return a.meta.tmb;
        }
    
        return null;
    }
    
    /** اختیاری: همان + URL کامل */
    function resolveThumbUrl(e, ART) {
        const t = resolveThumb(e, ART);
        return t ? UT + t + '.jpg' : '';
    }

    /**
     * تامب مخصوص artwork پلیر (چرخش مربعی):
     * اولویت: tmb → tmv → cvr → (fallback هنرمند مثل resolveThumb)
     */
    /**
     * پس‌زمینه media-stage: art → tmv → tmb
     */
    function resolveMediaStageBg(e) {
        if (e && e.meta) {
            if (e.meta.art) return e.meta.art;
            if (e.meta.tmv) return e.meta.tmv;
            if (e.meta.tmb) return e.meta.tmb;
        }
        const refs = e && Array.isArray(e.artists) ? e.artists : [];
        for (let i = 0; i < refs.length; i++) {
            const aid = typeof refs[i] === 'object' ? refs[i].id : refs[i];
            if (aid == null || !SN.artist || !SN.artist[aid]) continue;
            const a = SN.artist[aid];
            if (a.meta && a.meta.tmb) return a.meta.tmb;
        }
        return null;
    }

    function resolveMediaStageBgUrl(e) {
        const t = resolveMediaStageBg(e);
        return t ? UT + t + '.jpg' : '';
    }

    function resolvePlayerArtThumb(e) {
        if (e && e.meta) {
            if (e.meta.tmb) return e.meta.tmb;
            if (e.meta.tmv) return e.meta.tmv;
            if (e.meta.cvr) return e.meta.cvr;
        }
        // fallback: هنرمندهای آیتم
        const refs = e && Array.isArray(e.artists) ? e.artists : [];
        for (let i = 0; i < refs.length; i++) {
            const aid = typeof refs[i] === 'object' ? refs[i].id : refs[i];
            if (aid == null || !SN.artist || !SN.artist[aid]) continue;
            const a = SN.artist[aid];
            if (a.meta && a.meta.tmb) return a.meta.tmb;
        }
        return null;
    }

    function resolvePlayerArtThumbUrl(e) {
        const t = resolvePlayerArtThumb(e);
        return t ? UT + t + '.jpg' : '';
    }


    let svg_arrow_left = '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg>';
    let svg_arrow_right = '<svg style="width:20px;height:20px" version="1.1" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"></path><path d="M13 18l6 -6"></path><path d="M13 6l6 6"></path></svg>';
    
    function CCRL(sec, options) {

    options = Object.assign({
        step: "item",
        behavior: "smooth",
        keyboard: true,
        wheel: false,
        touch: false
    }, options || {});


    const container = sec.querySelector(".carousel-container");

    if (!container) {
        return null;
    }


    /*
     * --------------------------------------------------
     * Controller DOM
     * --------------------------------------------------
     */
     
    let touchStartX = 0;
    let touchStartY = 0;
    let touchMoved = false;

    const back = OA(document.createElement("div"), {
        className: "carousel-controller back"
    });

    const backContainer = OA(document.createElement("div"), {
        className: "carousel-button-container"
    });

    const backButton = OA(document.createElement("div"), {
        className: "carousel-button"
    });

    const next = OA(document.createElement("div"), {
        className: "carousel-controller next"
    });

    const nextContainer = OA(document.createElement("div"), {
        className: "carousel-button-container"
    });

    const nextButton = OA(document.createElement("div"), {
        className: "carousel-button"
    });


    const backIcon = OA(document.createElement("span"), {
        innerHTML: svg_arrow_left
    });

    const nextIcon = OA(document.createElement("span"), {
        innerHTML: svg_arrow_right
    });


    backButton.append(backIcon);
    nextButton.append(nextIcon);

    backContainer.append(backButton);
    nextContainer.append(nextButton);

    back.append(backContainer);
    next.append(nextContainer);

    sec.append(back, next);


    /*
     * --------------------------------------------------
     * Controller
     * --------------------------------------------------
     */

    const controller = {

        section: sec,

        container: container,

        back: backButton,

        next: nextButton,

        options: options,

        moveAmount: 0,


        /*
         * ----------------------------------------------
         * Calculate movement amount
         * ----------------------------------------------
         */

        calculateMoveAmount() {

            const items = container.children;

            if (!items.length) {
                return container.clientWidth;
            }


            if (options.step === "page") {

                return container.clientWidth;
            }


            if (items.length < 2) {

                return container.clientWidth;
            }


            const first = items[0];

            const second = items[1];


            const firstRect =
                first.getBoundingClientRect();

            const secondRect =
                second.getBoundingClientRect();


            const amount =
                Math.abs(
                    secondRect.left -
                    firstRect.left
                );


            return amount > 0
                ? amount
                : container.clientWidth;
        },


        /*
         * ----------------------------------------------
         * Update button state
         * ----------------------------------------------
         */

        update() {

            const maxScroll = Math.max(
                0,
                container.scrollWidth -
                container.clientWidth
            );
        
            const currentScroll =
                container.scrollLeft;
        
            const atStart =
                currentScroll <= 1;
        
            const atEnd =
                currentScroll >= maxScroll - 1;
        
            backButton.classList.toggle(
                "is-hidden",
                atStart
            );
        
            nextButton.classList.toggle(
                "is-hidden",
                atEnd
            );
        },


        /*
         * ----------------------------------------------
         * Next
         * ----------------------------------------------
         */

        next() {
            this.moveAmount = this.calculateMoveAmount();
        
            const maxScroll = Math.max(
                0,
                container.scrollWidth - container.clientWidth
            );
        
            const target = Math.min(
                container.scrollLeft + this.moveAmount,
                maxScroll
            );
        
            container.scrollTo({
                left: target,
                behavior: options.behavior
            });
        },


        /*
         * ----------------------------------------------
         * Previous
         * ----------------------------------------------
         */

        previous() {
            this.moveAmount = this.calculateMoveAmount();
        
            const target = Math.max(
                container.scrollLeft - this.moveAmount,
                0
            );
        
            container.scrollTo({
                left: target,
                behavior: options.behavior
            });
        },


        /*
         * ----------------------------------------------
         * Refresh
         * ----------------------------------------------
         */

        refresh() {

            this.moveAmount =
                this.calculateMoveAmount();

            this.update();
        },


        /*
         * ----------------------------------------------
         * Destroy
         * ----------------------------------------------
         */

        destroy() {

            nextButton.removeEventListener(
                "click",
                onNext
            );


            backButton.removeEventListener(
                "click",
                onBack
            );


            container.removeEventListener(
                "scroll",
                onScroll
            );

            if (options.touch) {
            
                container.removeEventListener(
                    "touchstart",
                    onTouchStart
                );
            
                container.removeEventListener(
                    "touchmove",
                    onTouchMove
                );
            
                container.removeEventListener(
                    "touchend",
                    onTouchEnd
                );
            }
            
            resizeObserver.disconnect();


            sec._carousel = null;
        }
    };


    /*
     * --------------------------------------------------
     * Events
     * --------------------------------------------------
     */

    function onNext() {
        controller.next();
    }


    function onBack() {
        controller.previous();
    }


    function onScroll() {
        controller.update();
    }
    
    function onTouchStart(e) {

        if (!options.touch) {
            return;
        }
    
        if (!e.touches || !e.touches.length) {
            return;
        }
    
        touchStartX =
            e.touches[0].clientX;
    
        touchStartY =
            e.touches[0].clientY;
    
        touchMoved = false;
    }
    
    function onTouchMove(e) {

        if (!options.touch) {
            return;
        }
    
        if (!e.touches || !e.touches.length) {
            return;
        }
    
        const x =
            e.touches[0].clientX;
    
        const y =
            e.touches[0].clientY;
    
        const deltaX =
            x - touchStartX;
    
        const deltaY =
            y - touchStartY;
    
        if (
            Math.abs(deltaX) >
            Math.abs(deltaY)
        ) {
            touchMoved = true;
        }
    }

    function onTouchEnd(e) {
    
        if (!options.touch) {
            return;
        }
    
        if (!e.changedTouches || !e.changedTouches.length) {
            return;
        }
    
        if (!touchMoved) {
            return;
        }
    
        const touchEndX =
            e.changedTouches[0].clientX;
    
        const deltaX =
            touchEndX - touchStartX;
    
        const threshold = 40;
    
        if (Math.abs(deltaX) < threshold) {
            return;
        }
    
        if (deltaX < 0) {
            controller.next();
        } else {
            controller.previous();
        }
    
        touchMoved = false;
    }

    nextButton.addEventListener(
        "click",
        onNext
    );


    backButton.addEventListener(
        "click",
        onBack
    );


    container.addEventListener(
        "scroll",
        onScroll
    );
    
    if (options.touch) {

        container.addEventListener(
            "touchstart",
            onTouchStart,
            { passive: true }
        );
    
        container.addEventListener(
            "touchmove",
            onTouchMove,
            { passive: true }
        );
    
        container.addEventListener(
            "touchend",
            onTouchEnd,
            { passive: true }
        );
    }


    const resizeObserver = new ResizeObserver(() => {
        controller.refresh();
    });
    
    resizeObserver.observe(container);


    /*
     * --------------------------------------------------
     * Store controller on section
     * --------------------------------------------------
     */

    sec._carousel = controller;


    /*
     * --------------------------------------------------
     * Initial state
     * --------------------------------------------------
     */

    requestAnimationFrame(() => {
        controller.refresh();
    });


    return controller;
}
    
    async function CRTS(QS,a){

        var a=0,ar;
        
        switch(QS) {
            case 'featured':
                a=1;
                ar=SN.featured_song;
                break;
            case 'latest':
            case 'trending':
            case 'popular':
                a=2;
                ar=SN.latest_song;
                break;
        }
        
        if(a!=0){
            const hed = OA(document.createElement("div"), {className: "main-tabs"});
            const ha1 = OA(document.createElement("a"), {className: "item"+(a==1?' active':''), href: "/song/featured", innerHTML:"Featured"});
            const ha2 = OA(document.createElement("a"), {className: "item"+(a==2?' active':''), href: "/song/latest", innerHTML:"Latest"});
            const ha3 = OA(document.createElement("a"), {className: "item"+(a==3?' active':''), href: "/song/trending", innerHTML:"Trending"});
            const ha4 = OA(document.createElement("a"), {className: "item"+(a==4?' active':''), href: "/song/popular", innerHTML:"Popular"});
            
            hed.append(ha1,ha2,ha3,ha4);
            MCNT.append(hed);
            CRGL(ar);
            ADE();
            
        }else{
            await CRSP(QS);
        }

    }
    
    async function CRPS(QS){

        if(QS=='favorite'){
            const hed = OA(document.createElement("div"), {className: "page-header m-top-1"});
            const hh1 = OA(document.createElement("h1"), {className: "title", innerHTML:'Your Favorite Songs'});
            hed.append(hh1);
            MCNT.append(hed);
            CRGL(SN.user.fv.sng);
            ADE();
            
        }else{
            // get playlist with json
            
        }

    }
    
    var MENU = document.querySelector(".menu");
    var HERO = document.querySelector("#hero");
    
    function UHERO(e,t){
    
        if(!e){return;}
        // HERO_UPDATE
        if(!MENU){return;}
        MENU.classList.contains("row")?'':MENU.classList.add("row");
        if(!HERO){return;}
        HERO.innerHTML = "";

        var dtn;
        //console.log(e);
        if(t=="artist"){
            dtn = OA(document.createElement("div"), {className: "thumbail artwork rounded-circle"});
        }else if(t=="musicvideo" || t=="video"){
            dtn = OA(document.createElement("div"), {className: "thumbail artwork ratio-16x9"});
        }else{
            dtn = OA(document.createElement("div"), {className: "thumbail artwork ratio-1x1"});
        }

        var thumb = resolveThumb(e) || null;
        var iep = OA(document.createElement("img"), {className: t == "artist" ? "ratio ratio-1x1 rounded-circle":"ratio ratio-1x1", src: thumb ? UT + thumb + ".jpg" : ""});
        dtn.appendChild(iep);

        const dct = OA(document.createElement("div"), {className: "content"});
        const tlt = OA(document.createElement("div"), {className: "title", innerHTML: e.name});
        dct.appendChild(tlt);

        var art = CRAL(e.artists);
        dct.appendChild(art);
            
        const dtl = OA(document.createElement("div"), {className: "detailes"});
        dct.appendChild(dtl);
        
        HERO.append(dtn,dct);
                
        if (e.meta && (e.meta.des || e.meta.rls || e.meta.bd || e.meta.tlg || e.meta.ins || e.meta.ncp != null || e.meta.dur != null)) {
            const abt = CRAB(e.meta);
            HERO.appendChild(abt);
        }
  
        ADE();

    }
    
    function Cmor(){
        
        const eM = MCNT.querySelectorAll(".menu");
        for (const e of eM) {
                e.addEventListener("click", (h)=>{
                     CRBW(e);
                },false);
        }
    }
    
    function rebuildPlaylistPicker(dbs, itemId, itemType, RmvDBW) {
        dbs.innerHTML = '';
        LoadProfile();
        const ids = (SN.profile.playlist || []).filter(
            (pid) => SN.playlist && SN.playlist[pid]
        );
    
        if (!ids.length) {
            renderCreatePlaylistForm(dbs, itemId, itemType, RmvDBW);
            return;
        }
    
        dbs.append(
            OA(document.createElement('div'), {
                className: 'item text2',
                innerHTML: 'افزودن به پلی‌لیست'
            })
        );
    
        renderPlaylistPicker(dbs, itemId, itemType, () => RmvDBW());
    
        const addNew = OA(document.createElement('div'), {
            className: 'item focusable m-top-1',
            innerHTML: svg_a2p ? svg_a2p + 'افزودن پلی‌لیست جدید' : 'افزودن پلی‌لیست جدید'
        });
        addNew.addEventListener('click', () => {
            renderCreatePlaylistForm(dbs, itemId, itemType, RmvDBW);
        });
        dbs.append(addNew);
    }
    
    function renderCreatePlaylistForm(dbs, itemId, itemType, RmvDBW) {
        dbs.innerHTML = '';
    
        const limits = SN.playlistLimits || { playlists: 5 };
        const serverCount = countCloudPlaylists(); // پایین
        const willBeLocalOnly =
            SN.user?.loggedIn &&
            serverCount >= (limits.playlists || 5);
    
        dbs.append(
            OA(document.createElement('div'), {
                className: 'item text2',
                innerHTML: 'ساخت پلی‌لیست جدید'
            })
        );
    
        if (willBeLocalOnly) {
            dbs.append(
                OA(document.createElement('div'), {
                    className: 'item text2 sheet-warning',
                    innerHTML:
                        'ظرفیت پلی‌لیست حساب پر است. این لیست فقط روی این دستگاه ذخیره می‌شود و با حساب همگام نمی‌شود.'
                })
            );
        }
    
        const nameInput = OA(document.createElement('input'), {
            className: 'input focusable',
            type: 'text',
            maxlength: 100,
            placeholder: 'نام پلی‌لیست'
        });
        
        const Brow = OA(document.createElement('div'), {className: 'button-row m-top-1'});
    
        const createBtn = OA(document.createElement('div'), {className: 'btn btn-model-3', innerHTML: 'Create'});
        createBtn.addEventListener('click', async () => {
            const name = nameInput.value.trim();
            if (!name) return;
            createBtn.innerHTML = '...';
    
            let pl;
            if (willBeLocalOnly) {
                pl = createLocalPlaylist(name);
            } else {
                pl = await createPlaylist(name);
                // createPlaylist در صورت playlist_limit باید createLocalPlaylist برگرداند
            }
    
            if (!pl) {
                createBtn.innerHTML = 'خطا';
                return;
            }
    
            if (itemId != null && itemType) {
                addItemToPlaylist(pl.id, itemId, itemType);
                RmvDBW();
                return;
            }
    
            rebuildPlaylistPicker(dbs, itemId, itemType, RmvDBW);
        });
    
        LoadProfile();
        const hasAny = (SN.profile.playlist || []).some(
            (pid) => SN.playlist && SN.playlist[pid]
        );
        if (hasAny && itemId) {
            const back = OA(document.createElement('div'), {className: 'btn btn-model-3', innerHTML: 'Back to List'});
            back.addEventListener('click', () => {
                rebuildPlaylistPicker(dbs, itemId, itemType, RmvDBW);
            });
            Brow.append(back, createBtn);
            dbs.append(nameInput, Brow);
        } else if (hasAny) {
            const back = OA(document.createElement('div'), {className: 'btn btn-model-3', innerHTML: 'Cancel'});
            back.addEventListener('click', () => {
                RmvDBW();
            });
            Brow.append(back, createBtn);
            dbs.append(nameInput, Brow);
        }
    
        setTimeout(() => nameInput.focus(), 50);
    }
    
    function renderDeletePlaylistConfirm(dbs, playlistId, RmvDBW) {
        dbs.innerHTML = '';
    
        dbs.append(
            OA(document.createElement('div'), {
                className: 'item text2',
                innerHTML: 'آیا از حذف کامل مطمئن هستید؟ با حذف پلی‌لیست، آیتم‌های آن نیز حذف می‌شوند.'
            })
        );
    
        const yes = OA(document.createElement('div'), {
            className: 'item focusable',
            innerHTML: 'بله، حذف شود'
        });
        const no = OA(document.createElement('div'), {
            className: 'item focusable',
            innerHTML: 'خیر'
        });
    
        yes.addEventListener('click', async () => {
            yes.innerHTML = '...';
            await deletePlaylistCompletely(playlistId);
            RmvDBW();
            // اگر روی صفحهٔ همان پلی‌لیست بودیم → برو library یا home
            const path = location.pathname || '';
            if (path.includes('/playlist/' + playlistId) || path.includes('/playlist/')) {
                chp('/library/playlist'); // یا chp() هوم
            }
        });
    
        no.addEventListener('click', () => {
            // برگشت به منوی عادی پلی‌لیست
            RmvDBW();
            CRBW(playlistId, 'playlist');
            // یا rebuild منو بدون بستن sheet — اگر dbs را دستی پر می‌کنی
        });
    
        dbs.append(yes, no);
    }
    
    function renderPlaylistSheetMenu(dbs, e, id, RmvDBW) {
        dbs.innerHTML = '';
    
        const btnPlay = OA(document.createElement('div'), {
            className: 'item focusable',
            innerHTML: svg_cpl + 'Play'
        });
        btnPlay.addEventListener('click', () => {
            RmvDBW();
            // PlayPlaylist(e);
        });
    
        const nodes = [btnPlay];
    
        if (isPlaylistOwner(e) && !e.system) {
            const btnVis = OA(document.createElement('div'), {
                className: 'item focusable',
                innerHTML: svg_cpl + (e.public ? 'Public' : 'Private')
            });
            btnVis.addEventListener('click', () => {
                togglePlaylistPublic(e, id, btnVis);
            });
    
            const btnEdit = OA(document.createElement('div'), {
                className: 'item focusable',
                innerHTML: svg_cpl + 'Edit'
            });
            btnEdit.addEventListener('click', () => {
                renderPlaylistEditForm(dbs, e, id, RmvDBW);
            });
    
            nodes.push(btnVis, btnEdit);
        }
    
        const isFav = IsFavorite('playlist', id);
        const btnFav = OA(document.createElement('div'), {
            className: 'item focusable',
            innerHTML: (isFav ? svg_fav2_m : svg_fav_m) + (isFav ? 'Unfollow' : 'Follow')
        });
        btnFav.addEventListener('click', () => {
            const now = ToggleFavorite('playlist', id);
            btnFav.innerHTML = (now ? svg_fav2_m : svg_fav_m) + (now ? 'Unfollow' : 'Follow');
        });
    
        const btnGo = OA(document.createElement('div'), {
            className: 'item',
            innerHTML: svg_g2s + 'Go to Playlist'
        });
        btnGo.addEventListener('click', () => {
            RmvDBW();
            chp(HPL + e.id + '/' + (e.url || ''));
        });
    
        nodes.push(btnFav, btnGo);
    
        if (isPlaylistOwner(e)) {
            const btnDel = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_r2p + 'Remove Playlist'
            });
            btnDel.addEventListener('click', () => {
                renderDeletePlaylistConfirm(dbs, id, RmvDBW);
            });
            nodes.push(btnDel);
        }
    
        const btnShare = OA(document.createElement('div'), {
            className: 'item focusable',
            innerHTML: svg_shr + 'Share'
        });
        btnShare.addEventListener('click', () => {
            const link =
                (typeof location !== 'undefined' ? location.origin : '') +
                HPL + e.id + '/' + (e.url || '');
            copyText(link, 'Playlist link copied');
        });
    
        const btnReport = OA(document.createElement('div'), {
            className: 'item focusable',
            innerHTML: svg_inf + 'Report'
        });
        btnReport.addEventListener('click', () => RmvDBW());
    
        nodes.push(btnShare, btnReport);
        dbs.append(...nodes);
    }
    
    function renderPlaylistEditForm(dbs, e, id, RmvDBW) {
        dbs.innerHTML = '';
    
        const nameInput = OA(document.createElement('input'), {
            className: 'input focusable',
            type: 'text',
            maxlength: 100,
            value: e.name || '',
            placeholder: 'نام پلی‌لیست'
        });
    
        const desInput = OA(document.createElement('textarea'), {
            className: 'input focusable',
            maxlength: 500,
            placeholder: 'توضیحات (اختیاری)'
        });
        desInput.value = (e.meta && e.meta.des) || '';
    
        const Brow = OA(document.createElement('div'), {className: 'button-row m-top-1'});
        
        const saveBtn = OA(document.createElement('div'), {className: 'btn btn-model-3', innerHTML: 'Create'});
        saveBtn.addEventListener('click', async () => {
            saveBtn.innerHTML = '...';
            const updated = await savePlaylistMeta(id, {
                name: nameInput.value,
                des: desInput.value
            });
            if (!updated) {
                saveBtn.innerHTML = 'خطا / نام خالی';
                return;
            }
            RmvDBW();
            if (typeof softRefreshPlaylistPage === 'function') {
                softRefreshPlaylistPage(id);
            }
        });
    
        const cancelBtn = OA(document.createElement('div'), {className: 'btn btn-model-3', innerHTML: 'Cancel'});
        cancelBtn.addEventListener('click', () => {
            renderPlaylistSheetMenu(dbs, e, id, RmvDBW);
        });
        
        Brow.append(cancelBtn, saveBtn);
    
        dbs.append(nameInput, desInput,Brow);
    }
    
    async function savePlaylistMeta(playlistId, opts) {
        const pl = SN.playlist && SN.playlist[playlistId];
        if (!pl) return null;
    
        const name = String(opts.name != null ? opts.name : pl.name || '').trim();
        if (!name) return null;
    
        const des = opts.des != null
            ? String(opts.des).trim()
            : ((pl.meta && pl.meta.des) || '');
    
        pl.name = name;
        pl.url = slugifyPlaylistName(name);
        if (!pl.meta) pl.meta = {};
        if (des) pl.meta.des = des;
        else delete pl.meta.des;
    
        if (typeof persistPlaylist === 'function') persistPlaylist(pl);
        else SaveProfile();
    
        const n = Number(playlistId);
        const isLocal = pl.local === true || (n >= 1 && n <= LOCAL_PLAYLIST_MAX);
    
        if (!isLocal && SN.user?.loggedIn && SN.accessToken && isPlaylistOwner(pl) && !pl.system) {
            const j = await gja({
                a: 'update_playlist',
                playlist_id: playlistId,
                name: name,
                des: des
                // بدون act
            });
            if (!j || j.error) return null;
            if (j.playlist && typeof ingestPlaylistObject === 'function') {
                await ingestPlaylistObject(j.playlist);
                return SN.playlist[playlistId] || j.playlist;
            }
        }
        return pl;
    }

    async function togglePlaylistPublic(pl, id, btnEl) {
        if (!pl || pl.system || !isPlaylistOwner(pl)) return;
    
        const next = !pl.public;
        const prev = pl.public;
        btnEl.innerHTML = svg_cpl + '...';
    
        // لوکال فوری
        pl.public = next;
        persistPlaylist(pl);
    
        const n = Number(id);
        const isLocal = pl.local === true || (n >= 1 && n <= LOCAL_PLAYLIST_MAX);
    
        if (!isLocal && SN.user?.loggedIn && SN.accessToken) {
            const j = await gja({
                a: 'set_playlist_public',
                playlist_id: id,
                public: next
            });
            if (!j || j.error) {
                pl.public = prev;
                persistPlaylist(pl);
                btnEl.innerHTML = svg_cpl + (prev ? 'Public' : 'Private');
                return;
            }
            if (typeof j.public === 'boolean') pl.public = j.public;
            persistPlaylist(pl);
        }
    
        btnEl.innerHTML = svg_cpl + (pl.public ? 'Public' : 'Private');
    }
    
    function renderReportFrom(e){
        const ctx = OA(document.createElement('div'), { className: 'content' });
        return ctx;
    }

    function renderArtistToBW(e) {
        const tm1 = OA(document.createElement('div'), { className: 'content' });
        if (e.artists && e.artists.length) {
            tm1.appendChild(CRAL(e.artists));
        } else {
            tm1.append(
                OA(document.createElement('div'), {
                    className: 'text2',
                    innerHTML: 'هنرمندی نیست'
                })
            );
        }
        return tm1;
    }

    // ---- owners DB: SN.owners + localStorage key "ha_owner" ----
    // فقط وقتی id قبلاً نبود اضافه می‌شود (بدون overwrite تکراری)
    const HA_OWNER_LS_KEY = 'ha_owner';

    function ensureOwnersMap() {
        if (!SN.owners || typeof SN.owners !== 'object' || Array.isArray(SN.owners)) {
            SN.owners = {};
            try {
                const raw = localStorage.getItem(HA_OWNER_LS_KEY);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                        SN.owners = parsed;
                    }
                }
            } catch (e) {}
        }
        // id=0 = Hamrahang — در لیست نام نشان داده نمی‌شود
        if (!SN.owners['0']) {
            SN.owners['0'] = { id: 0, name: 'Hamrahang', meta: {} };
        }
        return SN.owners;
    }

    function saveOwnersToLocal() {
        ensureOwnersMap();
        try {
            localStorage.setItem(HA_OWNER_LS_KEY, JSON.stringify(SN.owners));
        } catch (e) {}
    }

    function getOwner(pubId) {
        const id = Number(pubId) || 0;
        ensureOwnersMap();
        return SN.owners[String(id)] || null;
    }

    /** یک owner را فقط اگر قبلاً نبود اضافه می‌کند. true = اضافه شد */
    function putOwnerIfNew(o) {
        if (!o || o.id == null) return false;
        const id = Number(o.id);
        if (isNaN(id) || id < 0) return false;
        ensureOwnersMap();
        const key = String(id);
        if (SN.owners[key]) return false; // تکراری — skip
        const meta = (o.meta && typeof o.meta === 'object')
            ? o.meta
            : (o.photo ? { photo: String(o.photo) } : {});
        SN.owners[key] = {
            id: id,
            name: (id === 0) ? 'Hamrahang' : (o.name || ('User #' + id)),
            meta: meta
        };
        return true;
    }

    /** لیست owner — فقط جدیدها را merge می‌کند و در صورت تغییر LS را ذخیره می‌کند */
    function putOwners(list) {
        let added = 0;
        (list || []).forEach((o) => {
            if (putOwnerIfNew(o)) added++;
        });
        if (added > 0) saveOwnersToLocal();
        return added;
    }

    /**
     * از sources یک آیتم (song/video/...) ownerها را استخراج و دیتابیس می‌کند.
     * شکل سورس: { url, bit, owner: { id, name, meta? } } یا { user: id }
     */
    function collectOwnersFromItem(item) {
        if (!item || !Array.isArray(item.sources) || !item.sources.length) return 0;
        const batch = [];
        item.sources.forEach((s) => {
            if (!s) return;
            if (s.owner && typeof s.owner === 'object' && s.owner.id != null) {
                batch.push(s.owner);
            } else if (s.user != null) {
                // فقط id — نام بعداً با resolve یا از قبل در DB
                batch.push({ id: s.user, name: '' });
            }
        });
        return putOwners(batch);
    }

    /** owner id از یک سورس (object یا user id) */
    function sourceOwnerId(src) {
        if (!src) return 0;
        if (src.owner && typeof src.owner === 'object' && src.owner.id != null) {
            return Number(src.owner.id) || 0;
        }
        if (src.user != null) return Number(src.user) || 0;
        return 0;
    }

    async function resolveOwners(ids) {
        ensureOwnersMap();
        const need = [];
        const seen = {};
        (ids || []).forEach((raw) => {
            const id = Number(raw) || 0;
            if (id <= 0) return;
            if (seen[id]) return;
            seen[id] = true;
            if (!SN.owners[String(id)]) need.push(id);
        });
        if (!need.length) return SN.owners;
        try {
            const url = HP + '?_qjs=own&ids=' + need.join(',');
            const res = await fetch(url);
            if (res.ok) {
                const data = await res.json();
                if (data && Array.isArray(data.owners)) putOwners(data.owners);
            }
        } catch (err) {
            console.warn('[HA][resolveOwners]', err);
        }
        return SN.owners;
    }

    function bitBadgeClass(bit, isVideo) {
        const b = Number(bit) || 0;
        if (isVideo) {
            if (b >= 1024) return 'bit-badge bit-v-1024';
            if (b >= 720) return 'bit-badge bit-v-720';
            if (b >= 480) return 'bit-badge bit-v-480';
            return 'bit-badge bit-muted';
        }
        if (b >= 320) return 'bit-badge bit-a-320';
        if (b >= 128) return 'bit-badge bit-a-128';
        if (b >= 96) return 'bit-badge bit-a-96';
        return 'bit-badge bit-muted';
    }

    function formatBitLabel(bit, isVideo) {
        const b = Number(bit) || 0;
        if (!b) return '—';
        // ویدیو اغلب height/quality؛ صدا kbps
        if (isVideo && b >= 240) return String(b) + 'p';
        return String(b) + 'k';
    }

    /**
     * لیست سورس‌های دانلود داخل CRBW (مثل لیست هنرمند)
     * مرتب از بیشترین بیت به کمترین
     */
    async function renderDownloadSources(dbs, e, id, T, closeFn) {
        dbs.innerHTML = '';
        const wrap = OA(document.createElement('div'), { className: 'content download-sources' });
        const loading = OA(document.createElement('div'), {
            className: 'item text2',
            innerHTML: 'در حال بارگذاری سورس‌ها…'
        });
        wrap.append(loading);
        dbs.append(wrap);

        // اطمینان از لود کامل آیتم (sources)
        let item = e;
        try {
            if (!item || !Array.isArray(item.sources) || !item.sources.length) {
                if (T === 'song' || T === 'podcast') {
                    await gjs(['song', id]);
                    item = (SN.song && SN.song[id]) || (SN.podcast && SN.podcast[id]) || item;
                } else if (T === 'video' || T === 'musicvideo') {
                    await gjs(['video', id]);
                    item = (SN.video && SN.video[id]) || (SN.musicvideo && SN.musicvideo[id]) || item;
                }
            }
        } catch (err) {
            console.warn('[HA][renderDownloadSources] load item', err);
        }

        // ownerهای داخل sources را در DB محلی merge کن
        collectOwnersFromItem(item);

        const isVideo = (T === 'video' || T === 'musicvideo');
        let sources = (item && Array.isArray(item.sources)) ? item.sources.slice() : [];
        sources = sources.filter((s) => s && s.url);
        // بیت نزولی؛ در بیت یکسان original قبل از demo
        sources.sort((a, b) => {
            const bitDiff = (Number(b.bit) || 0) - (Number(a.bit) || 0);
            if (bitDiff !== 0) return bitDiff;
            const da = isDemoSource(a) ? 1 : 0;
            const db = isDemoSource(b) ? 1 : 0;
            return da - db;
        });

        // اگر owner فقط id داشت و در DB نبود، از API بگیر
        const missingIds = [];
        sources.forEach((s) => {
            const oid = sourceOwnerId(s);
            if (oid > 0 && !getOwner(oid)) missingIds.push(oid);
        });
        if (missingIds.length) await resolveOwners(missingIds);

        wrap.innerHTML = '';
        if (!sources.length) {
            wrap.append(
                OA(document.createElement('div'), {
                    className: 'item text2',
                    innerHTML: 'سورسی برای دانلود نیست'
                })
            );
            return;
        }

        const list = OA(document.createElement('div'), { className: 'download-source-list' });
        sources.forEach((src, i) => {
            const row = OA(document.createElement('div'), {
                className: 'download-source-row',
                title: src.url || ''
            });

            const oid = sourceOwnerId(src);
            // ترجیح: object داخل سورس، بعد DB
            let owner = (src.owner && typeof src.owner === 'object') ? src.owner : null;
            if (!owner || !owner.name) owner = getOwner(oid) || owner;

            // تامب
            const thumbBox = OA(document.createElement('div'), { className: 'ds-thumb' });
            const tmb = owner && owner.meta && owner.meta.tmb ? owner.meta.tmb : null;
            if (tmb) {
                thumbBox.append(
                    OA(document.createElement('img'), {
                        className: 'rounded-circle',
                        src: UT + tmb + '.jpg',
                        alt: ''
                    })
                );
            } else {
                thumbBox.append(
                    OA(document.createElement('span'), {
                        className: 'placeholder rounded-circle',
                        innerHTML: svg_dons
                    })
                );
            }
            row.append(thumbBox);

            // نام owner؛ برای id=0 / بدون نام → برند دامنهٔ URL (نه نام آیتم)
            // اگر demo باشد: «نام - demo»
            const mid = OA(document.createElement('div'), { className: 'ds-meta' });
            let labelName = '';
            if (oid !== 0 && owner && owner.name) {
                labelName = String(owner.name).trim();
            }
            if (!labelName) {
                labelName = hostLabelFromUrl(src.url);
            }
            if (isDemoSource(src)) {
                labelName = labelName ? (labelName + ' - demo') : 'demo';
            }
            if (labelName) {
                mid.append(
                    OA(document.createElement('span'), {
                        className: 'ds-owner-name',
                        innerHTML: labelName
                    })
                );
            }
            row.append(mid);

            // بیت در انتها
            const bit = Number(src.bit) || 0;
            const bitEl = OA(document.createElement('span'), {
                className: bitBadgeClass(bit, isVideo),
                innerHTML: formatBitLabel(bit, isVideo)
            });
            row.append(bitEl);

            row.addEventListener('click', () => {
                const baseName = (item && item.name) ? String(item.name) : ('file-' + id);
                let artistName = '';
                if (item && item.artist) artistName = String(item.artist) + ' - ';
                else if (item && Array.isArray(item.artists) && item.artists[0] && item.artists[0].name) {
                    artistName = String(item.artists[0].name) + ' - ';
                }
                const ext = isVideo ? '.mp4' : '.mp3';
                const fileName = (artistName + baseName).replace(/[\\/:*?"<>|]+/g, '_') + ext;
                console.log('[HA][download]', {
                    sourceIndex: i,
                    bit: bit,
                    ownerId: oid,
                    label: labelName || null,
                    url: String(src.url).slice(0, 80)
                });
                downloadFile(src.url, fileName);
                if (typeof closeFn === 'function') closeFn();
            });

            list.append(row);
        });
        wrap.append(list);
    }
    
    function closeBottomSheet(onDone) {
        const DBW = MCLN.querySelector('.bottom-sheet-wrapper');
        if (!DBW) {
            if (typeof onDone === 'function') onDone();
            return;
        }
        DBW.classList.add('translate-to-down');
        setTimeout(() => {
            DBW.remove();
            if (typeof onDone === 'function') onDone();
        }, 500);
    }

    /**
     * sheet را می‌سازد یا همان قبلی را برمی‌گرداند (بدون انیمیشن دوباره)
     */
    function ensureBottomSheet(opts) {
        opts = opts || {};
    
        if (opts.forceNew) {
            const old = MCLN.querySelector('.bottom-sheet-wrapper');
            if (old) old.remove();
        }
    
        let dbw = MCLN.querySelector('.bottom-sheet-wrapper');
    
        if (!dbw) {
            dbw = OA(document.createElement('div'), {
                className: 'bottom-sheet-wrapper translate-to-down'
            });
    
            const dbc = OA(document.createElement('div'), { className: 'bottom-sheet-container' });
            const hed = OA(document.createElement('div'), { className: 'bottom-sheet-header' });
            const cnt = OA(document.createElement('div'), { className: 'bottom-sheet-content' });
            const d10 = OA(document.createElement('div'), { style: 'width:100%;height:100%;' });
            const dbs = OA(document.createElement('div'), { className: 'bottom-sheet-menu' });
            d10.append(dbs);
            cnt.append(d10);
    
            const btc = OA(document.createElement('div'), {
                className: 'bottom-sheet-close',
                innerHTML: 'Close'
            });
            btc.addEventListener('click', () => closeBottomSheet());
    
            dbc.append(hed, cnt, btc);
            dbw.append(dbc);
            MCLN.append(dbw);
    
            // انیمیشن ورود
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    dbw.classList.remove('translate-to-down');
                });
            });
        }
    
        const hed = dbw.querySelector('.bottom-sheet-header');
        const dbs = dbw.querySelector('.bottom-sheet-menu');
        const btc = dbw.querySelector('.bottom-sheet-close');
        if (btc) btc.classList.toggle('vis-none', !!opts.hideClose);
    
        return { dbw, hed, dbs };
    }
    
    function setSheetHeader(hed, e, T, fallbackTitle) {
        hed.innerHTML = '';
        if (!e) {
            const dtx = OA(document.createElement('div'), { className: 'texts' });
            dtx.append(
                OA(document.createElement('div'), {
                    innerHTML: fallbackTitle || 'Hamrahang'
                })
            );
            hed.append(dtx);
            return;
        }
    
        let TPS = svg_sng;
        if (T === 'playlist') TPS = svg_pls;
        if (T === 'artist') TPS = svg_sng; // یا آیکون خودت
    
        const daw = OA(document.createElement('div'), { className: 'artwork' });
        const dph = OA(document.createElement('div'), {
            className: 'placeholder',
            innerHTML: TPS
        });
        const thumb = resolveThumb(e);
        daw.append(dph);
        if (thumb) {
            daw.append(
                OA(document.createElement('img'), { src: UT + thumb + '.jpg' })
            );
        }
    
        const dtx = OA(document.createElement('div'), { className: 'texts' });
        dtx.append(OA(document.createElement('div'), { innerHTML: e.name || '' }));
    
        const artistRefs = Array.isArray(e.artists) ? e.artists : [];
        if (artistRefs.length) {
            const tx2 = OA(document.createElement('div'), {
                className: 'text2 artist artists-inline'
            });
            artistRefs.forEach((ref, j) => {
                const aid = typeof ref === 'object' ? ref.id : ref;
                const a = SN.artist && SN.artist[aid];
                if (!a) return;
                tx2.append(
                    OA(document.createElement('span'), { innerHTML: a.name })
                );
                if (j < artistRefs.length - 1) {
                    tx2.append(
                        OA(document.createElement('span'), { innerHTML: ' - ' })
                    );
                }
            });
            dtx.append(tx2);
        }
    
        hed.append(daw, dtx);
    }
    
    function renderItemSheetMenu(dbs, e, id, T, ctx, RmvDBW) {
        dbs.innerHTML = '';
        ctx = ctx || {};
    
        let url = HS;
        if (T === 'song' || T === 'podcast') url = HS;
        else if (T === 'album') url = HL;
        else if (T === 'artist') url = HA;
        else if (T === 'playlist') url = HPL;
        else if (T === 'video' || T === 'musicvideo') url = HV;
    
        const artistList = [];
        let primaryArtistUrl = '';
        const artistRefs = Array.isArray(e.artists) ? e.artists : [];
        artistRefs.forEach((artistRef) => {
            const artistId = typeof artistRef === 'object' ? artistRef.id : artistRef;
            if (SN.artist && SN.artist[artistId]) {
                artistList.push(SN.artist[artistId]);
            }
        });
        if (artistList.length > 0) {
            primaryArtistUrl = artistList[0].url || '';
        }
    
        const nodes = [];
    
        const tm1 = OA(document.createElement('div'), {
            className: 'item',
            innerHTML: svg_cpl + (e.meta && e.meta.ncp != null ? e.meta.ncp + ' Plays' : 'Plays')
        });
        nodes.push(tm1);
        
        const isFav = IsFavorite(T, id);
    
        if (PLAYABLE_TYPES.indexOf(T) !== -1) {
            
            const btnLike = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: (isFav ? svg_lik2 : svg_lik) + (isFav ? 'Unlike' : 'Like')
            });
            btnLike.addEventListener('click', () => {
                const now = ToggleFavorite(T, id);
                btnLike.innerHTML = (now ? svg_lik2 : svg_lik) + (now ? 'Unlike' : 'Like');
                // sheet را باز بگذار تا وضعیت دیده شود
            });
            nodes.push(btnLike);

            const btnQ = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_a2p + 'Add to Queue'
            });
            btnQ.addEventListener('click', () => {
                addToPlayQueue(id, T);
                RmvDBW();
            });
            nodes.push(btnQ);
    
            const tm2 = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_a2p + 'Add to Playlist'
            });
            tm2.addEventListener('click', () => {
                // بدون بستن sheet — اگر ensureBottomSheet داری
                CRBW(id, T, 'pls', ctx);
            });
            nodes.push(tm2);
        } else {
            const btnFav = OA(document.createElement('div'), {
                className: 'item',
                innerHTML:
                    (isFav ? svg_fav2_m : svg_fav_m) +
                    (isFav ? 'Following' : 'Follow')
            });
            btnFav.addEventListener('click', () => {
                const now = ToggleFavorite(T, id);
                btnFav.innerHTML =
                    (now ? svg_fav2_m : svg_fav_m) + (now ? 'Following' : 'Follow');
            });
            nodes.push(btnFav);
        }
    
        const containing = findPlaylistsContainingItem(id, T);
        if (containing.length) {
            const trm = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_r2p + 'Remove From Playlist'
            });
            trm.addEventListener('click', () => {
                if (ctx.playlistId && containing.map(Number).includes(Number(ctx.playlistId))) {
                    removeItemFromPlaylist(ctx.playlistId, id, T);
                    RmvDBW();
                    softRefreshPlaylistPage(ctx.playlistId);
                    return;
                }
                CRBW(id, T, 'rmpls', ctx);
            });
            nodes.push(trm);
        }
    
        // فقط اگر هنرمند دارد
        if (artistList.length > 0 || (Array.isArray(e.artists) && e.artists.length > 0)) {
            const tm3 = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_g2a + 'Go to artist'
            });
            tm3.addEventListener('click', () => {
                closeBottomSheet(() => {
                    CRBW(id, T, 'art', { fresh: true });
                });
            });
            nodes.push(tm3);
        }
    
        const tm4 = OA(document.createElement('div'), {
            className: 'item',
            innerHTML: svg_g2s + 'Go to ' + T
        });
        tm4.addEventListener('click', () => {
            RmvDBW();
            chp(url + e.id + '/' + (T !== 'artist' ? primaryArtistUrl + '-' : '') + (e.url || ''));
        });
        nodes.push(tm4);
    
        // Download — فقط برای آیتم‌های قابل پخش؛ مثل Go to artist روی CRBW باز می‌شود
        if (PLAYABLE_TYPES.indexOf(T) !== -1) {
            const btnDon = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_don + 'Download'
            });
            btnDon.addEventListener('click', () => {
                closeBottomSheet(() => {
                    CRBW(id, T, 'don', { fresh: true });
                });
            });
            nodes.push(btnDon);
        }

        const btnShare = OA(document.createElement('div'), {
            className: 'item',
            innerHTML: svg_shr + 'Share'
        });
        nodes.push(btnShare);

        const Brp = OA(document.createElement('div'), { className: 'item', innerHTML: svg_inf + 'Report' });
        nodes.push(Brp);
        Brp.addEventListener('click', () => {
            RmvDBW();
            dbs.innerHTML = '';
            dbs.appendChild(renderReportFrom(id));
            return;
        });
    
        dbs.append(...nodes);
    }
    
    function showSyncPrompt(onDone) {
        CRBW(null, null, 'msg', {
            title: 'همگام‌سازی',
            render: (dbs, close) => {
                dbs.append(
                    OA(document.createElement('div'), {
                        className: 'item text2',
                        innerHTML:
                            'اطلاعات این دستگاه (علاقه‌مندی‌ها و پلی‌لیست‌ها) با حساب همگام شود؟ فقط موارد جدید اضافه می‌شوند؛ چیزی پاک نمی‌شود.'
                    })
                );
    
                const Brow = OA(document.createElement('div'), {
                    className: 'button-row m-top-1'
                });
                const btnNo = OA(document.createElement('div'), {
                    className: 'btn btn-model-3',
                    innerHTML: 'الان نه'
                });
                const btnYes = OA(document.createElement('div'), {
                    className: 'btn btn-model-3',
                    innerHTML: 'همگام‌سازی'
                });
    
                btnNo.addEventListener('click', () => {
                    close();
                    if (onDone) onDone(false);
                });
                btnYes.addEventListener('click', () => {
                    close();
                    if (onDone) onDone(true);
                });
    
                Brow.append(btnNo, btnYes);
                dbs.append(Brow);
            }
        });
    }
    
    function CRBW(id, T, c, ctx) {
        ctx = ctx || {};
        c = c || null;
        
        const forceNew =
        !!ctx.fresh ||
        c === 'add' ||
        c === 'alert' ||
        c === 'msg' ||
        c === 'art' ||
        c === 'don' ||
        c === 'pls' ||
        c === 'rmpls' ||
        !c;
    
        // ---- حالت هشدار / پیام (کنسول برای کاربر) ----
        if (c === 'alert') {
            const { hed, dbs } = ensureBottomSheet({
                forceNew: true,
                hideClose: !!ctx.hideClose
            });
            setSheetHeader(hed, null, null, ctx.title || 'توجه');
        
            dbs.innerHTML = '';
            dbs.append(
                OA(document.createElement('div'), {
                    className: 'item text2',
                    innerHTML: ctx.message || ''
                })
            );
        
            const Brow = OA(document.createElement('div'), {
                className: 'button-row m-top-1'
            });
            const ok = OA(document.createElement('div'), {
                className: 'btn btn-model-3',
                innerHTML: ctx.okText || 'متوجه شدم'
            });
            ok.addEventListener('click', () => {
                closeBottomSheet();
                if (typeof ctx.onOk === 'function') ctx.onOk();
            });
            Brow.append(ok);
            dbs.append(Brow);
            return;
        }
        
        if (c === 'msg') {
            const { hed, dbs } = ensureBottomSheet({
                forceNew: true,
                hideClose: !!ctx.hideClose
            });
            setSheetHeader(hed, null, null, ctx.title || 'پیام');
        
            dbs.innerHTML = '';
        
            if (typeof ctx.render !== 'function') {
                dbs.append(
                    OA(document.createElement('div'), {
                        className: 'item text2',
                        innerHTML: 'محتوایی تعریف نشده'
                    })
                );
                return;
            }
        
            // dbs خالی + closeBottomSheet در اختیار سازنده
            ctx.render(dbs, closeBottomSheet);
            return;
        }
    
        // ---- ساخت پلی‌لیست: id می‌تواند null باشد ----
        if (T === 'playlist' && c === 'add') {
            const e = id != null && SN.playlist && SN.playlist[id] ? SN.playlist[id] : null;
            const { hed, dbs } = ensureBottomSheet({
                forceNew: true,
                hideClose: false
            });
            setSheetHeader(hed, e, 'playlist', 'پلی‌لیست جدید');
            renderCreatePlaylistForm(dbs, ctx.itemId || null, ctx.itemType || null, closeBottomSheet);
            return;
        }
    
        // ---- بقیه نیاز به موجودیت دارند ----
        if (id == null || !T || !SN[T] || !SN[T][id]) return;

        const e = SN[T][id];
        const { hed, dbs } = ensureBottomSheet({
            forceNew: forceNew,
            hideClose: !!ctx.hideClose
        });
        setSheetHeader(hed, e, T);
    
        // ----- route -----
        if (c === 'art') {
            dbs.innerHTML = '';
            dbs.appendChild(renderArtistToBW(e));
            ADE();
            return;
        }

        if (c === 'don') {
            dbs.innerHTML = '';
            renderDownloadSources(dbs, e, id, T, closeBottomSheet);
            return;
        }
    
        if (c === 'pls') {
            rebuildPlaylistPicker(dbs, id, T, closeBottomSheet);
            return;
        }
    
        if (c === 'rmpls') {
            // همان منطق قبلی remove-from-playlist در dbs
            renderRemoveFromPlaylistBody(dbs, id, T, ctx, closeBottomSheet);
            return;
        }
        
        if (T === 'playlist' && c === 'edit') {
            renderPlaylistEditForm(dbs, e, id, closeBottomSheet);
            return;
        }
    
        if (T === 'playlist' && c === 'del') {
            renderDeletePlaylistConfirm(dbs, id, closeBottomSheet);
            return;
        }
        
        if (T === 'playlist' && !c) {
            renderPlaylistSheetMenu(dbs, e, id, closeBottomSheet);
            return;
        }
    
        renderItemSheetMenu(dbs, e, id, T, ctx, closeBottomSheet);
        
        
    }
    /*function CRBW(id, T, c, ctx){

        if (!SN[T][id]) return;
        ctx = ctx || {};
        
        const dbw = OA(document.createElement("div"), {className: "bottom-sheet-wrapper"});
        MCLN.append(dbw);
        
        const dbc = OA(document.createElement("div"), {className: "bottom-sheet-container"});
        const hed = OA(document.createElement("div"), {className: "bottom-sheet-header"});
        dbc.append(hed);
        dbw.append(dbc);
        
        let url = HS;
        let TPS = svg_sng;

        if (T == 'song') { url = HS; }
        if (T == 'album') { url = HL; }
        if (T == 'artist') { url = HA; }
        if (T == 'playlist') { url = HPL; TPS = svg_pls;}

        const e = SN[T][id];

        let  artistList = [];
        let  primaryArtistUrl = "";

        const artistRefs = (e.artists && Array.isArray(e.artists)) ? e.artists : [];
        artistRefs.forEach((artistRef) => {

            const artistId =
                typeof artistRef === "object"
                    ? artistRef.id
                    : artistRef;

            if (SN.artist && SN.artist[artistId]) {
                artistList.push(SN.artist[artistId]);
            }
        });

        if (artistList.length > 0) {
            primaryArtistUrl = artistList[0].url || "";
        }

        const daw = OA(document.createElement("div"), {className: "artwork"});
        const dph = OA(document.createElement("div"), {className: "placeholder", innerHTML: TPS });
       
        const thumb = resolveThumb(e);
        if (thumb) {
            daw.append(dph, OA(document.createElement("img"), {src: UT + thumb + ".jpg"}));
        } else {
            daw.append(dph);
        }
        const dtx = OA(document.createElement("div"), {className: "texts"});
        const tx1 = OA(document.createElement("div"), {innerHTML: e.name});
        
        const tx2 = OA(document.createElement("div"), {className: "text2 artist artists-inline"});
        artistList.forEach((artistObj, j) => {
            const artN = OA(document.createElement("span"), {innerHTML: artistObj.name});
            tx2.append(artN);
        
            if (j < artistList.length - 1) {
                const sepSpan = OA(document.createElement("span"), {innerHTML: " - "});
                tx2.append(sepSpan);
            }
        });
        dtx.append(tx1,tx2)
        hed.append(daw,dtx);
        
        const cnt = OA(document.createElement("div"), {className: "bottom-sheet-content"});
        const d10 = OA(document.createElement("div"), {style: "width: 100%; height: 100%;"});
        cnt.append(d10);
        const dbs = OA(document.createElement("div"), {className: "bottom-sheet-menu"});
        d10.append(dbs);
        
        if(c==='art'){
            dbs.innerHTML = '';
            dbs.appendChild(renderArtistToBW(e));
            
        }else if (c === 'pls') {
            // عنوان اختیاری
            dbs.append(
                OA(document.createElement('div'), {
                    className: 'item text2',
                    innerHTML: 'افزودن به پلی‌لیست'
                })
            );
            renderPlaylistPicker(dbs, id, T, () => {
                RmvDBW();
            });
            rebuildPlaylistPicker(dbs, id, T, RmvDBW);
        }else if (c === 'rmpls') {
            const pids = findPlaylistsContainingItem(id, T);
            dbs.append(
                OA(document.createElement('div'), {
                    className: 'item text2',
                    innerHTML: 'حذف از پلی‌لیست'
                })
            );
            if (!pids.length) {
                dbs.append(
                    OA(document.createElement('div'), {
                        className: 'text2',
                        innerHTML: 'در هیچ پلی‌لیستی نیست'
                    })
                );
            } else {
                pids.forEach((pid) => {
                    const pl = SN.playlist[pid];
                    const row = OA(document.createElement('div'), {
                        className: 'item focusable',
                        innerHTML: (pl && pl.name) ? pl.name : ('Playlist ' + pid)
                    });
                    row.addEventListener('click', async () => {
                        removeItemFromPlaylist(pid, id, T);
                        RmvDBW();
                        // اگر روی همان پلی‌لیست بودیم
                        if (ctx.playlistId && Number(ctx.playlistId) === Number(pid)) {
                            await softRefreshPlaylistPage(pid);
                        }
                    });
                    dbs.append(row);
                });
            }
        }else if (T === 'playlist' && !c) {
            renderPlaylistSheetMenu(dbs, e, id, RmvDBW);
        }else if (c === 'edit' && T === 'playlist') {
            //RmvDBW();
            // CRBW(id, T, 'rmpls', ctx);
            renderPlaylistEditForm(dbs, e, id, RmvDBW);
        }else if (c === 'del' && T === 'playlist') {
            renderDeletePlaylistConfirm(dbs, id, RmvDBW);
        }else{
            const tm1 = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_cpl + (e.meta?.ncp != null ? e.meta.ncp + ' Plays' : 'Plays')
            });
            

            
    
            const containing = findPlaylistsContainingItem(id, T);
            const trm = OA(document.createElement('div'), {
                className: 'item',
                innerHTML: svg_r2p + 'Remove From Playlist'
            });
                // فقط اگر حداقل در یک پلی‌لیست باشد
            if (containing.length) {
                trm.addEventListener('click', () => {
                    // اگر ctx.playlistId هست و فقط همان را می‌خواهی مستقیم حذف کنی:
                    if (ctx.playlistId && containing.map(Number).includes(Number(ctx.playlistId))) {
                        removeItemFromPlaylist(ctx.playlistId, id, T);
                        RmvDBW();
                        softRefreshPlaylistPage(ctx.playlistId);
                        return;
                    }
                    RmvDBW();
                    CRBW(id, T, 'rmpls', ctx);
                });
            }    
                

                const tm3 = OA(document.createElement("div"), {className: "item", innerHTML: svg_g2a+'Go to artist'});
                tm3.addEventListener("click", ()=>{
                        RmvDBW();
                        CRBW(id,T,'art');
                        return false;
                },false);
                
                const tm4 = OA(document.createElement("div"), {className: "item", innerHTML: svg_g2s+'Go to '+T});
                tm4.addEventListener("click", ()=>{
                        RmvDBW();
                        chp(url + e.id + '/' + (T !== "artist" ? primaryArtistUrl + "-" : "") + e.url);
                        return false;
                },false);
                
                const tm5 = OA(document.createElement("div"), {className: "item", innerHTML: svg_don+'Download'});
                const tm6 = OA(document.createElement("div"), {className: "vis-none", innerHTML: svg_cmt+'View Comments'});
                const tm7 = OA(document.createElement("div"), {className: "item", innerHTML: svg_lik+'View Likes'});
                const tm8 = OA(document.createElement("div"), {className: "item", innerHTML: svg_shr+'Share'});
                const tm9 = OA(document.createElement("div"), {className: "item", innerHTML: svg_inf+'Report'});
                const tm10 = OA(document.createElement("div"), {className: "vis-none", innerHTML: svg_app+'Open in app'});
                
                const dbr = OA(document.createElement("div"), {className: "download-bar vis-nt"});
                const bar = OA(document.createElement("div"), {className: "download-bar-content"});
                const s128 = OA(document.createElement("div"), {className: "down-item",innerHTML: '128'});
                const s320 = OA(document.createElement("div"), {className: "down-item",innerHTML: '320'});
                const stlg = OA(document.createElement("div"), {className: "down-item",innerHTML: '<span style="margin-right:10px" >To</span>'+svg_tl2});
                bar.append(s128,s320,stlg);
                dbr.append(bar);
                
                const nodes = [tm1];
                
                if (PLAYABLE_TYPES.indexOf(T) !== -1) {
                    const btnQ = OA(document.createElement('div'), {
                        className: 'item',
                        innerHTML: svg_a2p + 'Add to Queue'
                    });
                    btnQ.addEventListener('click', () => {
                        addToPlayQueue(id, T);
                        RmvDBW();
                    });
                    nodes.push(btnQ)
                    
                    const tm2 = OA(document.createElement('div'), {
                    className: 'item',
                        innerHTML: svg_a2p + 'Add to Playlist'
                    });
                    tm2.addEventListener('click', () => {
                        RmvDBW();
                        CRBW(id, T, 'pls', ctx);
                    });
                    nodes.push(tm2)
                }
                
                
                if (containing.length) nodes.push(trm);
                dbs.append(...nodes,tm3,tm4,dbr,tm5,tm7,tm8,tm9);
            }
        
        const btc = OA(document.createElement("div"), {className: "bottom-sheet-close", innerHTML: "Close" });
        btc.addEventListener("click", ()=>{
                    RmvDBW();
        });
        
        dbc.append(cnt,btc);


        
        const BWC = DBW.querySelector(".bottom-sheet-close");
        const BMI = DBW.querySelectorAll(".bottom-sheet-menu .item");
        const BDB = DBW.querySelector(".bottom-sheet-menu .download-bar");
        const BDI = DBW.querySelectorAll(".download-bar-content .down-item");
       //  href: HS+e.artist_url+'-'+e.url, 

        

        
        BMI[4].addEventListener("click", ()=>{
            
            (TGAP.platform !='unknown')?
            (SN.user.ap)?Send2TG(q):SrtBot()
            :
            (BDB.classList.contains("vis-nt"))?BDB.classList.remove("vis-nt"):BDB.classList.add("vis-nt");
            return false;
        });

        
     
        BDI[0].addEventListener("click", ()=>{
                downloadFile(q.audio[0].url,q.artist+' - '+q.name,'.mp3');
                return false;
        },false);
        
        BDI[2].addEventListener("click", ()=>{
                (SN.user.ap)?Send2TG(q):SrtBot();
                return false;
        },false);
        
        BMI[6].addEventListener("click", ()=>{
            //    (SN.user.ap)?Send2TG(q):SrtBot();
                (SN.user.id)?(parent.location.href = 'https://t.me/share/url?url=https://t.me/HamrahangBot?startapp='+q.artist_url+'-'+q.url+'==ref'+SN.user.id+'&text=Listen To '+q.name+' By '+q.artist): copyText(HSU+q.artist_url+'-'+q.url,'Song link copied to clipboard');
                return false;
        },false);
        
        
        

        
    }*/
    
    function AddPls(){
            
        const drs = OA(document.createElement("div"), {className: "download-dialog show_fadein"});
        const cnt = OA(document.createElement("div"), {className: "content"});
        const txt = OA(document.createElement("div"), {className: "texts"});
        cnt.append(txt);
        const tx1 = OA(document.createElement("div"), {className: "text1", innerHTML: "Add New Playlist"});
        
        const df4 = OA(document.createElement("div"), {className: "input-container"});
        const iab = OA(document.createElement("input"), {className: "input" ,type: "text",id:"playlist" ,maxlength: 100, placeholder:"Playlist Name", autocorrect:"off", autocapitalize:"off", spellcheck:"false"});
        df4.append(iab);

        const btn = OA(document.createElement("div"), {className: "buttons m-top-1"});
        const bta = OA(document.createElement("span"), {className: "btn btn-sm btn-model-4", innerHTML: "Done"});
        const bca = OA(document.createElement("span"), {className: "btn btn-sm btn-model-4", innerHTML: "Cancel"});
        btn.append(bta,bca);
        txt.append(tx1,df4,btn);
        drs.append(cnt);
        MCLN.append(drs);
        
        const DRS = MCLN.querySelector(".download-dialog");
        const BSA = MCLN.querySelector(".download-dialog a.btn-sm");
        bta.addEventListener("click", ()=>{
            setTimeout(function() {
                drs.remove();
            }, 500);
        });

        bca.addEventListener("click", ()=>{
     /*               
        const tms = OA(document.createElement("div"), {className: "toast-message show"});
        const spn = OA(document.createElement("span"), {innerHTML:t});
        tms.append(spn);
        document.body.append(tms);*/
            drs.className = drs.className.replace("show_fadein", "show_fadeout");
            setTimeout(function() {
               drs.remove();
            }, 500);
            
        });
        
    }

    function SrtBot(){
            
        const drs = OA(document.createElement("div"), {className: "download-dialog show_fadein"});
        const cnt = OA(document.createElement("div"), {className: "content"});
        const txt = OA(document.createElement("div"), {className: "texts"});
        cnt.append(txt);
        const tx1 = OA(document.createElement("div"), {className: "text1", innerHTML: "Start The Hamrahang First"});
        const btn = OA(document.createElement("div"), {className: "buttons m-top-1"});
        const bta = OA(document.createElement("a"), {className: "btn btn-sm btn-model-4", href: "https://t.me/HamrahangBot", target: "_blank", innerHTML: "Start App"});
        btn.append(bta);
        txt.append(tx1,btn);
        drs.append(cnt);
        MCLN.append(drs);
        
        const DRS = MCLN.querySelector(".download-dialog");
        const BSA = MCLN.querySelector(".download-dialog a.btn-sm");
        BSA.addEventListener("click", ()=>{
            setTimeout(function() {
                DRS.remove();
                window.Telegram.WebApp.close();
            }, 500);
        });
        
    }
    
    async function Send2TG(q) {

        await fetch(HP+'?_qjs=tg', {
            method: "POST",
            body: JSON.stringify({
                id:SN.user.id,
                q
            }),
            headers: {
                "Content-type": "application/json; charset=UTF-8"
            }
        })
        .then((response) => response.json())
        .then((j) => Res(j));
    }
   
    function downloadFile(urlOrData, name = 'file', type) {
        // url مستقیم سورس (حالت اصلی دانلود از لیست)
        if (typeof urlOrData === 'string' && /^https?:\/\//i.test(urlOrData)) {
            const a = document.createElement('a');
            a.href = urlOrData;
            a.setAttribute('download', name || 'file');
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            setTimeout(() => { try { a.remove(); } catch (e) {} }, 1000);
            return;
        }
        // سازگاری با مسیر قدیمی CDN
        const anchor = document.createElement('a');
        const ext = type || '.mp3';
        anchor.setAttribute('href', CDNMP3 + name + ext);
        anchor.setAttribute('download', name);
        anchor.style.display = 'none';
        document.body.appendChild(anchor);
        anchor.click();
        setTimeout(() => { try { anchor.remove(); } catch (e) {} }, 1000);
    }
    
    function Res(j){
        
        if(j.ok==true){
            
            console.log(j);
            CRTM('Downloaded In Telegram');
            const DBW = MCLN.querySelector(".bottom-sheet-wrapper");
            DBW.classList.add("translate-to-down");
            setTimeout(function() {
                DBW.remove();
            }, 500);
            
            
            const drs = OA(document.createElement("div"), {className: "response-wrapper"});
            const spn = OA(document.createElement("span"), {innerHTML: "Sent to Telegram"});
            drs.append(spn);
            MCLN.append(drs);
            
            setTimeout(function() {
                drs.remove();
            }, 2000);
        }
        
    }    

    function ADE(){
        
        var AA = [];
        const ABox = document.querySelectorAll(".artist-box a");
        const ALnk = document.querySelectorAll(".artists a");
        const TBox = document.querySelectorAll(".track-box a");
        const TLin = document.querySelectorAll(".track-line a");
        const SAll = document.querySelectorAll(".section-header a");
        const MTab = document.querySelectorAll(".main-tabs a");
        const MENA = document.querySelectorAll(".menu a");
        const STab = document.querySelectorAll(".setting-tabs a");
        const SArt = document.querySelectorAll(".artists-list a");
        const MCAL = document.querySelectorAll(".main-container a");
        
        
        AA.push.apply(AA, MENA);
        AA.push.apply(AA, ABox);
        AA.push.apply(AA, ALnk);
        AA.push.apply(AA, TBox);
        AA.push.apply(AA, TLin);
        AA.push.apply(AA, MTab);
        AA.push.apply(AA, SAll);
        AA.push.apply(AA, STab);
        AA.push.apply(AA, SArt);
        AA.push.apply(AA, MCAL);
        
        
        
        const pH = document.querySelector(".navbar-m .menu").children[0];
        const pB = document.querySelector(".navbar-m .menu").children[1];
        const pS = document.querySelector(".navbar-m .menu").children[2];
        const pA = document.querySelector(".navbar-m .menu").children[3];
        const Arr_np = [pH,pB,pS];

        AA.push.apply(AA, Arr_np);

        //console.log(AA);

         for (const e of AA) {
             
        //     e.removeEventListener("click", chp(), false); // Succeeds
             
            if (e.getAttribute('listener') !== 'true') {
                e.setAttribute('listener', 'true');
                e.addEventListener("click", (h)=>{
                h.preventDefault();
                chp(e.href);
                return false;
                },false);

            }
        }
       
        AA = [];
    }

    function Cap(st) {
        return st.split(' ').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
    }

    function Crsl() {
    
        const sections = document.querySelectorAll(
            ".section-carousel-container"
        );
    
        sections.forEach((section) => {
    
            if (section._carousel) {
                return;
            }
    
            const controller =
                section.querySelector(
                    ".carousel-controller"
                );
    
            if (!controller) {
                return;
            }
    
            const container =
                section.querySelector(
                    ".carousel-container"
                );
    
            if (!container) {
                return;
            }
    
            section._carousel = {
                section: section,
                container: container
            };
        });
    }

    function ECN(e) {

        e.cloneNode(true);
    }

    function FSRC() {    // SEARCH //
        var SCNT = document.querySelectorAll(".search-container");
        SCNT.forEach((e) =>{

            const SFM = e.querySelector("form");
            const SSB = e.querySelector("button");
            ECN(SFM);ECN(SSB);
            if (SSB.getAttribute('listener') !== 'true') {
                SSB.setAttribute('listener', 'true');
                SFM.addEventListener("submit",  (e) => { e.preventDefault();SRC_IN(SFM); }, false); 
                SSB.addEventListener("click",  (e) => {SRC_IN(SFM); }, false);
            }
        });
        
        function SRC_IN(f) {
                const q = f.querySelector("input").value;   
                const p = HSR+q;
                chp(p);
        }

    }
    
    // ===================== PLAYERS =====================
    
    const MinPly = document.querySelector('.minimize-player-wrapper');
    const MinPlyBtn = MinPly ? MinPly.querySelectorAll('.button') : [];
    const MinArt = MinPly ? MinPly.querySelector('.artwork') : null;
    const MinTxs = MinPly ? MinPly.querySelector('.texts') : null;
    const MinTx1 = MinPly ? MinPly.querySelector('.text1') : null;
    const MinTx2 = MinPly ? MinPly.querySelector('.text2') : null;
    const MinSkb = MinPly ? MinPly.querySelector('.seekbar') : null;
    
    const MaxPly = document.querySelector('.maximum-player-wrapper');
    const MaxMobPly = MaxPly ? MaxPly.querySelector('.maximum-player-mobile') : null;
    const MaxMobMin = MaxMobPly ? MaxMobPly.querySelector('.minimize') : null;
    const MaxArt = MaxMobPly ? MaxMobPly.querySelector('.artwork') : null;
    const MaxTx1 = MaxMobPly ? MaxMobPly.querySelector('.texts .text1') : null;
    const MaxTx2 = MaxMobPly ? MaxMobPly.querySelector('.texts .text2') : null;
    const MaxSkb = MaxMobPly ? MaxMobPly.querySelector('.seekbar input') : null;
    const MaxMobBtn = MaxMobPly ? MaxMobPly.querySelectorAll('.buttons .button') : [];
    const MaxTim = MaxMobPly ? MaxMobPly.querySelectorAll('.times span') : [];
    
    const MaxDeskPly = document.querySelector('.maximum-player-desktop');
    const MaxDeskArt = MaxDeskPly ? MaxDeskPly.querySelector('.artwork') : null;
    const MaxDeskTx1 = MaxDeskPly ? MaxDeskPly.querySelector('.text1') : null;
    const MaxDeskTx2 = MaxDeskPly ? MaxDeskPly.querySelector('.text2') : null;
    const MaxDeskBtns = MaxDeskPly ? MaxDeskPly.querySelectorAll('.controll .button') : [];
    const MaxDeskDtls = MaxDeskPly ? MaxDeskPly.querySelectorAll('.details .button') : [];
    
    const MaxQue = document.querySelector('.maximum-player-queue');
    const MaxQueHdt = MaxQue ? MaxQue.querySelector('.header .title') : null;
    const MaxQueHdrBtns = MaxQue ? MaxQue.querySelectorAll('.header .buttons .button') : [];
    const MaxQueList = MaxQue ? MaxQue.querySelector('.track-line-queue-container') : null;
    const MaxQueCls = MaxQue ? MaxQue.querySelector('.bottom-sheet-close') : null;
    
    // ---- state یکجا ----
    function defaultPlayerState() {
        return {
            queue: [],
            queueIndex: -1,
            nowPlaying: null,
            repeat: 'off',
            shuffle: false,
            playedInShuffle: {},
            skipAttempts: 0,
            playGen: 0,
            waiting: false
        };
    }
    
    function ensurePlayer() {
        if (!SN.player || typeof SN.player !== 'object' || Array.isArray(SN.player)) {
            SN.player = defaultPlayerState();
        }
        const P = SN.player;
        if (!Array.isArray(P.queue)) P.queue = [];
        if (typeof P.queueIndex !== 'number') P.queueIndex = -1;
        if (!P.playedInShuffle || typeof P.playedInShuffle !== 'object') P.playedInShuffle = {};
        if (P.repeat !== 'off' && P.repeat !== 'one' && P.repeat !== 'all') P.repeat = 'off';
        if (typeof P.shuffle !== 'boolean') P.shuffle = false;
        if (typeof P.skipAttempts !== 'number') P.skipAttempts = 0;
        if (typeof P.playGen !== 'number') P.playGen = 0;
        if (typeof P.waiting !== 'boolean') P.waiting = false;
        return P;
    }
    
    ensurePlayer(); // اول از همه
    ensureOwnersMap(); // بارگذاری ha_owner از localStorage به SN.owners
    
    // ---- queue ----
    function normalizeQueueRows(list, defaultType) {
        if (!Array.isArray(list) || !list.length) return [];
        return list
            .map((row, i) => {
                if (row && typeof row === 'object' && row.id != null) {
                    return {
                        id: row.id,
                        type: row.type || defaultType || 'song',
                        position: row.position != null ? row.position : i
                    };
                }
                return { id: row, type: defaultType || 'song', position: i };
            })
            .filter((r) => PLAYABLE_TYPES.indexOf(r.type || 'song') !== -1)
            .sort((a, b) => a.position - b.position)
            .map((r, i) => ({ id: r.id, type: r.type, position: i }));
    }
    
    function queueItemKey(row) {
        if (!row) return '';
        return String(row.type || 'song') + ':' + String(row.id);
    }
    
    function resetShuffleMemory() {
        ensurePlayer().playedInShuffle = {};
    }
    
    function onQueueReplaced() {
        resetShuffleMemory();
    }
    
    function markPlayed(row) {
        if (!row) return;
        const P = ensurePlayer();
        P.playedInShuffle[queueItemKey(row)] = true;
    }
    
    function recordPlayHistory(row) {
        if (!row || row.id == null) return;
        const type = row.type || 'song';
        if (PLAYABLE_TYPES.indexOf(type) === -1) return;
    
        const P = ensurePlayer();
        const key = queueItemKey(row);
        if (P._lastHistoryKey === key) return; // rebuffer / seek
        P._lastHistoryKey = key;
    
        pushPlayHistory(row.id, type);
    }
    
    async function setPlayQueue(list, startId, defaultType) {
        const P = ensurePlayer();
        let rows = normalizeQueueRows(list, defaultType);
        // فقط تایپ‌های قابل پخش
        const beforeFilter = rows.length;
        rows = rows.filter((r) => PLAYABLE_TYPES.indexOf(r.type || 'song') !== -1);
        if (rows.length !== beforeFilter) {
            console.log('[HA][setPlayQueue] filtered non-playable', { before: beforeFilter, after: rows.length });
        }
        // position را بعد از فیلتر دوباره ببند
        rows.forEach((r, i) => { r.position = i; });
        P.queue = rows;
    
        let idx = 0;
        if (startId != null) {
            const found = rows.findIndex((r) => Number(r.id) === Number(startId));
            if (found >= 0) idx = found;
            else console.log('[HA][setPlayQueue] startId not found, fallback 0', { startId });
        }
        P.queueIndex = rows.length ? idx : -1;
        onQueueReplaced();
        CRQT();
    
        if (P.queueIndex < 0) return rows;
    
        const row = rows[P.queueIndex];
        const np = P.nowPlaying;
        const already =
            np &&
            Number(np.id) === Number(row.id) &&
            np.type === row.type &&
            Number(np.index) === Number(P.queueIndex);
    
        // همان آیتم: به‌جای هیچ‌کار، toggle play/pause
        if (already) {
            console.log('[HA][setPlayQueue] same item → TogglePlayPause', { id: row.id, type: row.type });
            TogglePlayPause();
            return rows;
        }
    
        await playQueueAt(P.queueIndex);
        return rows;
    }
    
    function addToPlayQueue(itemId, itemType) {
        const P = ensurePlayer();
        const type = itemType || 'song';
        if (P.queue.some((r) => Number(r.id) === Number(itemId) && r.type === type)) return false;
        P.queue.push({ id: itemId, type, position: P.queue.length });
        CRQT();
        return true;
    }
    
    function removeFromPlayQueue(itemId, itemType) {
        const P = ensurePlayer();
        if (!P.queue.length) return false;
        const type = itemType || 'song';
        const before = P.queue.length;
        const wasIndex = P.queue.findIndex(
            (r) => Number(r.id) === Number(itemId) && r.type === type
        );
        P.queue = P.queue.filter(
            (r) => !(Number(r.id) === Number(itemId) && r.type === type)
        );
        if (P.queue.length === before) return false;
        P.queue.forEach((r, i) => { r.position = i; });
        if (wasIndex >= 0) {
            if (P.queueIndex === wasIndex) {
                P.queueIndex = Math.min(wasIndex, P.queue.length - 1);
            } else if (P.queueIndex > wasIndex) {
                P.queueIndex -= 1;
            }
        }
        if (!P.queue.length) P.queueIndex = -1;
        CRQT();
        return true;
    }
    
    function moveInPlayQueue(itemId, itemType, dir) {
        const P = ensurePlayer();
        if (!P.queue.length) return false;
        const type = itemType || 'song';
        const idx = P.queue.findIndex(
            (r) => Number(r.id) === Number(itemId) && r.type === type
        );
        if (idx < 0) return false;
        const j = idx + dir;
        if (j < 0 || j >= P.queue.length) return false;
        const cur = P.queueIndex;
        const tmp = P.queue[idx];
        P.queue[idx] = P.queue[j];
        P.queue[j] = tmp;
        P.queue.forEach((r, i) => { r.position = i; });
        if (cur === idx) P.queueIndex = j;
        else if (cur === j) P.queueIndex = idx;
        CRQT();
        return true;
    }
    
    /**
     * رندر مشترک صف پخش
     * @param {HTMLElement} container
     * @param {{ compact?: boolean }} [opts]
     */

    function formatQueueDate(meta) {
        if (!meta) return '';
        const raw = meta.rls || meta.bd || '';
        if (!raw) return '';
        // already YYYY-MM-DD
        if (/^\d{4}-\d{2}-\d{2}/.test(raw)) return String(raw).slice(0, 10);
        const d = new Date(raw);
        if (isNaN(d.getTime())) return String(raw).slice(0, 10);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function formatQueueDuration(meta) {
        if (!meta || meta.dur == null || meta.dur === '') return '';
        const sec = Number(meta.dur);
        if (!Number.isFinite(sec) || sec < 0) return '';
        return FormatTime(sec);
    }

    /** نام‌های فرعی آیتم (غیر از name اصلی) با جداکننده - */
    function itemAltNamesHtml(item) {
        if (!item) return '';
        const primary = (item.name || '').trim().toLowerCase();
        const alts = [];
        if (item.names && typeof item.names === 'object') {
            Object.keys(item.names).forEach((k) => {
                const v = String(item.names[k] || '').trim();
                if (!v) return;
                if (v.toLowerCase() === primary) return;
                if (alts.indexOf(v) === -1) alts.push(v);
            });
        }
        if (!alts.length) return '';
        return '<span class="alt-names"> - ' + alts.join(' - ') + '</span>';
    }

    function renderQueueItems(container, opts) {
        if (!container) return;
        opts = opts || {};
        const compact = !!opts.compact;
        const P = ensurePlayer();
        const ar = P.queue || [];
        container.innerHTML = '';
        if (!ar.length) {
            if (compact) {
                container.append(OA(document.createElement('div'), {
                    className: 'text2',
                    innerHTML: 'Queue is empty'
                }));
            }
            return;
        }

        ar.forEach((row, qi) => {
            const type = row.type || 'song';
            let item = null;
            let itemUrl = HS;
            if (type === 'song') { item = SN.song && SN.song[row.id]; itemUrl = HS; }
            else if (type === 'podcast') { item = SN.podcast && SN.podcast[row.id]; itemUrl = HS; }
            else if (type === 'musicvideo') { item = SN.musicvideo && SN.musicvideo[row.id]; itemUrl = HV; }
            else if (type === 'video') { item = SN.video && SN.video[row.id]; itemUrl = HV; }
            if (!item) return;

            const artistList = [];
            let primaryArtistUrl = '';
            (Array.isArray(item.artists) ? item.artists : []).forEach((ref) => {
                const aid = typeof ref === 'object' ? ref.id : ref;
                if (SN.artist && SN.artist[aid]) artistList.push(SN.artist[aid]);
            });
            if (artistList.length) primaryArtistUrl = artistList[0].url || '';

            const isCurrent =
                P.queueIndex >= 0 &&
                P.queue[P.queueIndex] &&
                Number(P.queue[P.queueIndex].id) === Number(row.id) &&
                P.queue[P.queueIndex].type === type;

            if (compact) {
                const rowEl = OA(document.createElement('div'), {
                    className: 'player-queue-row' + (isCurrent ? ' playing' : '')
                });
                const num = OA(document.createElement('div'), { className: 'pq-num' });
                num.innerHTML = isCurrent ? svg_eql : String(qi + 1);
                rowEl.append(num);

                const thumb = OA(document.createElement('div'), { className: 'pq-thumb' });
                let tmb = item.meta && item.meta.tmb ? item.meta.tmb : null;
                if (!tmb && artistList.length === 1 && artistList[0].meta) tmb = artistList[0].meta.tmb || null;
                if (tmb) thumb.append(OA(document.createElement('img'), { src: UT + tmb + '.jpg', alt: '' }));
                else thumb.append(OA(document.createElement('span'), { className: 'placeholder', innerHTML: svg_sng }));
                const playIdx = () => {
                    const idx = P.queue.findIndex((r) => Number(r.id) === Number(row.id) && r.type === type);
                    if (idx >= 0) playQueueAt(idx);
                };
                thumb.addEventListener('click', playIdx);
                rowEl.append(thumb);

                const meta = OA(document.createElement('div'), { className: 'pq-meta' });
                meta.append(OA(document.createElement('div'), {
                    className: 'n1',
                    innerHTML: (item.name || '') + itemAltNamesHtml(item)
                }));
                const aNames = artistList.map((a) => a.name).filter(Boolean).join(' - ');
                if (aNames) meta.append(OA(document.createElement('div'), { className: 'n2', innerHTML: aNames }));
                meta.addEventListener('click', playIdx);
                rowEl.append(meta);

                // ستون تاریخ و مدت — عرض ثابت برای هم‌ترازی
                rowEl.append(OA(document.createElement('div'), {
                    className: 'pq-date',
                    innerHTML: formatQueueDate(item.meta) || '&nbsp;'
                }));
                rowEl.append(OA(document.createElement('div'), {
                    className: 'pq-dur',
                    innerHTML: formatQueueDuration(item.meta) || '&nbsp;'
                }));

                const acts = OA(document.createElement('div'), { className: 'pq-actions' });
                const btnUp = OA(document.createElement('div'), { className: 'more-btn', innerHTML: svg_aup, title: 'Up' });
                btnUp.addEventListener('click', (ev) => { ev.stopPropagation(); moveInPlayQueue(row.id, type, -1); });
                const btnDown = OA(document.createElement('div'), { className: 'more-btn', innerHTML: svg_adw, title: 'Down' });
                btnDown.addEventListener('click', (ev) => { ev.stopPropagation(); moveInPlayQueue(row.id, type, +1); });
                acts.append(btnUp, btnDown);
                rowEl.append(acts);

                const rm = OA(document.createElement('div'), { className: 'pq-remove', innerHTML: 'Remove' });
                rm.addEventListener('click', (ev) => { ev.stopPropagation(); removeFromPlayQueue(row.id, type); });
                rowEl.append(rm);

                container.append(rowEl);
                return;
            }

            // full track-line (max queue)
            const el = OA(document.createElement('div'), {
                className: 'track-line' + (isCurrent ? ' playing' : '')
            });
            const ela = OA(document.createElement('a'), {
                href: itemUrl + item.id + '/' + primaryArtistUrl + '-' + (item.url || '')
            });
            const artwork = OA(document.createElement('div'), { className: 'artwork ratio ratio-1x1' });
            artwork.append(OA(document.createElement('span'), { className: 'placeholder' }));
            let tmb2 = item.meta && item.meta.tmb ? item.meta.tmb : null;
            if (!tmb2 && artistList.length === 1 && artistList[0].meta) tmb2 = artistList[0].meta.tmb || null;
            if (tmb2) artwork.append(OA(document.createElement('img'), { src: UT + tmb2 + '.jpg' }));
            ela.append(artwork);

            const dpl = OA(document.createElement('div'), {
                className: 'play',
                innerHTML: isCurrent ? svg_eql : svg_ply
            });
            dpl.addEventListener('click', () => {
                const idx = P.queue.findIndex((r) => Number(r.id) === Number(row.id) && r.type === type);
                if (idx >= 0) playQueueAt(idx);
            });

            const dst = OA(document.createElement('div'), { className: 'texts' });
            const det = OA(document.createElement('div'), { className: 'title' });
            const titleA = OA(document.createElement('a'), {
                draggable: 'false',
                href: itemUrl + item.id + '/' + primaryArtistUrl + '-' + (item.url || ''),
                innerHTML: (item.name || '') + itemAltNamesHtml(item)
            });
            det.append(titleA);
            const dt2 = OA(document.createElement('div'), { className: 'text2 artist artists-inline' });
            artistList.forEach((artistObj, j) => {
                const artL = OA(document.createElement('a'), {
                    href: HA + artistObj.id + '/' + (artistObj.url || '')
                });
                artL.append(OA(document.createElement('span'), { innerHTML: artistObj.name }));
                dt2.append(artL);
                if (j < artistList.length - 1) dt2.append(OA(document.createElement('span'), { innerHTML: ' - ' }));
            });
            dst.append(det, dt2);

            const dmt = OA(document.createElement('div'), { className: 'meta' });
            dmt.append(
                OA(document.createElement('div'), {
                    className: 'date',
                    innerHTML: formatQueueDate(item.meta) || ''
                }),
                OA(document.createElement('div'), {
                    className: 'duration',
                    innerHTML: formatQueueDuration(item.meta) || ''
                })
            );
            const dbr = OA(document.createElement('div'), { className: 'btn btn-model-3', innerHTML: 'Remove' });
            dbr.addEventListener('click', () => removeFromPlayQueue(row.id, type));
            dmt.append(dbr);

            const dma = OA(document.createElement('div'), { className: 'more-arrow' });
            const btnUp2 = OA(document.createElement('div'), { className: 'more-btn', innerHTML: svg_aup });
            btnUp2.addEventListener('click', () => moveInPlayQueue(row.id, type, -1));
            const btnDown2 = OA(document.createElement('div'), { className: 'more-btn', innerHTML: svg_adw });
            btnDown2.addEventListener('click', () => moveInPlayQueue(row.id, type, +1));
            dma.append(btnUp2, btnDown2);

            const mor = OA(document.createElement('div'), { className: 'more', innerHTML: svg_dot });
            mor.addEventListener('click', () => CRBW(item.id, type, null, { queue: true }));

            el.append(ela, dpl, dst, dmt, dma, mor);
            container.append(el);
        });
    }

    function CRQT() {
        const P = ensurePlayer();
        const ar = P.queue || [];
        if (MaxQueHdt) MaxQueHdt.innerHTML = 'Next Playing (' + ar.length + ')';
        if (MaxQueList) renderQueueItems(MaxQueList, { compact: false });

        // رفرش پنل پلیر اگر روی تب queue است
        if (P._playerPanelMode === 'queue') {
            const body = document.getElementById('player-panel-body');
            const title = document.getElementById('player-panel-title');
            if (title) title.innerHTML = 'Next Playing (' + ar.length + ')';
            if (body) {
                body.innerHTML = '';
                renderPlayerQueuePanel(body);
            }
        }
    }

    // ---- media helpers ----
    function playerKind(type) {
        return (type === 'video' || type === 'musicvideo') ? 'video' : 'audio';
    }
    
    function GetActiveMediaElement() {
        const P = ensurePlayer();
        if (P.nowPlaying && P.nowPlaying.player === 'video') return Video;
        return Audio;
    }
    
    function FormatTime(seconds) {
        if (!seconds || isNaN(seconds) || seconds < 0) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return m + ':' + (s < 10 ? '0' : '') + s;
    }
    
    function getItemFromQueueRow(row) {
        if (!row) return null;
        const t = row.type || 'song';
        if (t === 'song' && SN.song && SN.song[row.id]) return SN.song[row.id];
        if (t === 'podcast' && SN.podcast && SN.podcast[row.id]) return SN.podcast[row.id];
        if (t === 'musicvideo' && SN.musicvideo && SN.musicvideo[row.id]) return SN.musicvideo[row.id];
        if (t === 'video' && SN.video && SN.video[row.id]) return SN.video[row.id];
        if ((t === 'song' || t === 'podcast') && SN.song && SN.song[row.id]) return SN.song[row.id];
        if ((t === 'song' || t === 'podcast') && SN.podcast && SN.podcast[row.id]) return SN.podcast[row.id];
        if ((t === 'video' || t === 'musicvideo') && SN.musicvideo && SN.musicvideo[row.id]) return SN.musicvideo[row.id];
        if ((t === 'video' || t === 'musicvideo') && SN.video && SN.video[row.id]) return SN.video[row.id];
        return null;
    }
    
    async function ensureQueueItemLoaded(row) {
        let item = getItemFromQueueRow(row);
        if (item && Array.isArray(item.sources) && item.sources.length) {
            collectOwnersFromItem(item);
            return item;
        }
        const t = row.type || 'song';
        try {
            if (t === 'song' || t === 'podcast') await gjs(['song', row.id]);
            else await gjs(['video', row.id]);
        } catch (e) {
            console.warn('ensureQueueItemLoaded', e);
        }
        item = getItemFromQueueRow(row);
        if (item) collectOwnersFromItem(item);
        return item;
    }
    
    function UpdateAllPlayButtons() {
        const P = ensurePlayer();
        const np = P.nowPlaying;
        const waiting = !!P.waiting;
        const buffering = !!(np && np.isBuffering);
        const playing = !!(np && np.isPlaying && !waiting);
    
        const base = playing ? svg_paus : svg_plys;
        const baseMini = playing ? svg_pus : svg_ply;

        const showSpin = !!P.waiting || !!(np && np.isBuffering);
        const icon = showSpin ? base + '<i class="wlap"></i>' : base;
        const iconMini = showSpin ? baseMini + '<i class="wlap"></i>' : baseMini;
    
        document.querySelectorAll('.play-pause').forEach((btn) => {
            btn.innerHTML = icon;
            btn.classList.toggle('loading', showSpin || buffering);
            btn.classList.toggle('is-playing', playing);
        });
    
        if (MinPlyBtn && MinPlyBtn[1]) {
            MinPlyBtn[1].innerHTML = iconMini;
            MinPlyBtn[1].classList.toggle('loading', showSpin || buffering);
        }
    
        setArtSpinning(playing && !waiting && !buffering);
    }
    

    /**
     * LyricsEngine — موتور مشترک LRC / متن ثابت
     * داده از item.lyrics[]: { lng, knd, fmt, src, body }
     * خروجی: mount روی چند container با کلاس .ha-lrc
     */
    const LyricsEngine = {
        lines: [],
        activeIndex: -1,
        offsetMs: 0,
        itemId: null,
        itemType: null,
        lang: 'fa',
        synced: false,
        displayMode: 'normal', // normal | line | focus | card
        _mounts: [], // { el, mode: 'sync'|'static' }
        _lineFadeToken: 0,

        getDisplayMode() {
            try {
                return GetSetting('lyricDisplayMode') || this.displayMode || 'normal';
            } catch (e) {
                return this.displayMode || 'normal';
            }
        },

        setDisplayMode(mode) {
            this.displayMode = mode || 'normal';
            try { SetSetting('lyricDisplayMode', this.displayMode); } catch (e) {}
            this.activeIndex = -1; // اجبار به رفرش خط فعال بعد از تعویض استایل
            this._rerenderAll();
            try {
                const media = GetActiveMediaElement();
                const t = media ? (media.currentTime || 0) : 0;
                this.sync(t);
            } catch (e) {}
        },

        parseLrc(text) {
            const out = [];
            if (!text) return out;
            const re = /^\[(\d{1,2}):(\d{2})(?:[\.:](\d{1,3}))?\]\s*(.*)$/;
            String(text).split(/\r?\n/).forEach((raw) => {
                const line = raw.trim();
                if (!line) return;
                // meta tags [ti:] [ar:] [offset:] ...
                const meta = line.match(/^\[([a-zA-Z]+):(.*)\]$/);
                if (meta && !/^\d/.test(meta[1])) {
                    if (meta[1].toLowerCase() === 'offset') {
                        const n = parseInt(meta[2], 10);
                        if (Number.isFinite(n)) this.offsetMs = n;
                    }
                    return;
                }
                const m = line.match(re);
                if (!m) {
                    // خط بدون زمان → متن ثابت (t = -1 نگه می‌داریم در parsePlain)
                    return;
                }
                const min = parseInt(m[1], 10) || 0;
                const sec = parseInt(m[2], 10) || 0;
                let frac = 0;
                if (m[3]) {
                    const f = m[3];
                    frac = parseInt(f, 10) / (f.length >= 3 ? 1000 : 100);
                }
                const t = min * 60 + sec + frac;
                const txt = (m[4] || '').trim();
                if (txt) out.push({ t, text: txt });
            });
            out.sort((a, b) => a.t - b.t);
            return out;
        },

        parsePlain(text) {
            return String(text || '')
                .split(/\r?\n/)
                .map((s) => s.trim())
                .filter(Boolean)
                .map((txt) => ({ t: -1, text: txt }));
        },

        /** انتخاب بهترین lyrics entry از item */
        pickEntry(item, preferredLng) {
            if (!item) return null;
            const arr = Array.isArray(item.lyrics) ? item.lyrics : [];
            if (!arr.length) return null;
            const lng = preferredLng || this.lang || 'fa';
            let hit = arr.find((x) => x && x.body && (x.lng === lng || (x.lng || '').startsWith(lng)));
            if (!hit) hit = arr.find((x) => x && x.body);
            return hit || null;
        },

        hasLyrics(item) {
            return !!this.pickEntry(item);
        },

        loadFromItem(item, preferredLng) {
            this.lines = [];
            this.activeIndex = -1;
            this.offsetMs = 0;
            this.synced = false;
            this.itemId = item && item.id != null ? item.id : null;
            this.itemType = (item && item.meta && item.meta.type) || null;
            const entry = this.pickEntry(item, preferredLng);
            if (!entry || !entry.body) {
                this._rerenderAll();
                return false;
            }
            const knd = String(entry.knd || '').toLowerCase();
            const body = entry.body;
            if (knd === 'lrc' || /^\s*\[\d{1,2}:\d{2}/.test(body)) {
                this.lines = this.parseLrc(body);
                this.synced = this.lines.length > 0 && this.lines[0].t >= 0;
            } else {
                this.lines = this.parsePlain(body);
                this.synced = false;
            }
            if (entry.lng) this.lang = entry.lng;
            this._rerenderAll();
            return this.lines.length > 0;
        },

        clear() {
            this.lines = [];
            this.activeIndex = -1;
            this.synced = false;
            this.itemId = null;
            this._rerenderAll();
        },

        findIndex(timeSec) {
            if (!this.synced || !this.lines.length) return -1;
            const t = timeSec + (this.offsetMs / 1000);
            // آخرین خطی که t_line <= t
            let lo = 0, hi = this.lines.length - 1, ans = -1;
            while (lo <= hi) {
                const mid = (lo + hi) >> 1;
                if (this.lines[mid].t <= t) {
                    ans = mid;
                    lo = mid + 1;
                } else {
                    hi = mid - 1;
                }
            }
            return ans;
        },

        sync(timeSec) {
            if (!this.synced) return;
            const idx = this.findIndex(timeSec);
            if (idx === this.activeIndex) return;
            this.activeIndex = idx;
            this._applyActive();
        },

        /** ثبت container — mode: sync|static ، viewOverride: normal|line|focus|card|null */
        mount(el, mode, viewOverride) {
            if (!el) return;
            mode = mode || (this.synced ? 'sync' : 'static');
            const view = (viewOverride != null && viewOverride !== '') ? viewOverride : null;
            const exists = this._mounts.find((m) => m.el === el);
            if (exists) {
                exists.mode = mode;
                exists.view = view;
            } else {
                this._mounts.push({ el, mode, view });
            }
            this._renderInto(el, mode, view);
            this._applyActive();
        },

        unmount(el) {
            this._mounts = this._mounts.filter((m) => m.el !== el);
        },

        /** همهٔ .lyric و #media-stage-lyric صفحه را پیدا و mount کن */
        mountDefaults(item) {
            if (item) this.loadFromItem(item);
            const stage = document.getElementById('media-stage-lyric');
            // stage از تنظیم پلیر پیروی می‌کند (بدون viewOverride)
            if (stage) this.mount(stage, this.synced ? 'sync' : 'static', null);
            // صفحه سانگ/جزئیات: همیشه نرمال — مستقل از lyricDisplayMode پلیر
            document.querySelectorAll('.page-detail-header-container .lyric, .lyric[data-ha-lrc]').forEach((el) => {
                this.mount(el, this.synced ? 'sync' : 'static', this.synced ? 'normal' : 'static');
            });
            // دکمه lyric
            const btn = document.getElementById('lyric');
            if (btn) {
                if (this.lines.length) btn.classList.remove('vis-none');
                else btn.classList.add('vis-none');
            }
        },

        _rerenderAll() {
            this._mounts = this._mounts.filter((m) => m.el && m.el.isConnected);
            this._mounts.forEach((m) => this._renderInto(m.el, m.mode, m.view));
            this._applyActive();
        },

        _viewFor(mode, viewOverride) {
            if (mode === 'static' || !this.synced) return 'static';
            if (viewOverride) return viewOverride;
            return this.getDisplayMode();
        },

        _renderInto(el, mode, viewOverride) {
            if (!el) return;
            el.innerHTML = '';
            if (!this.lines.length) {
                el.append(OA(document.createElement('div'), {
                    className: 'ha-lrc-empty',
                    innerHTML: 'No lyrics'
                }));
                return;
            }
            const view = this._viewFor(mode, viewOverride);
            // --- خطی / کارت: اسکلت سبک؛ متن در _applyActive ---
            if (view === 'line' || view === 'card') {
                const wrap = OA(document.createElement('div'), {
                    className: 'ha-lrc ha-lrc--' + view
                });
                if (view === 'card') {
                    const box = OA(document.createElement('div'), { className: 'ha-lrc-card-box' });
                    const stage = OA(document.createElement('div'), { className: 'ha-lrc-line-stage' });
                    stage.append(OA(document.createElement('div'), {
                        className: 'ha-lrc-fade is-in',
                        innerHTML: ''
                    }));
                    box.append(stage);
                    wrap.append(box);
                } else {
                    const stage = OA(document.createElement('div'), { className: 'ha-lrc-line-stage' });
                    stage.append(OA(document.createElement('div'), {
                        className: 'ha-lrc-fade is-in',
                        innerHTML: ''
                    }));
                    wrap.append(stage);
                }
                el.append(wrap);
                return;
            }
            // --- نرمال / فوکوس: همه خطوط ---
            const wrap = OA(document.createElement('div'), {
                className: 'ha-lrc' + (view === 'focus' ? ' ha-lrc--focus' : '') + (view === 'static' ? ' static' : '')
            });
            this.lines.forEach((ln, i) => {
                const row = OA(document.createElement('div'), {
                    className: 'ha-lrc-line',
                    innerHTML: ln.text
                });
                row.setAttribute('data-i', String(i));
                if (ln.t >= 0) row.setAttribute('data-t', String(ln.t));
                if (this.synced && ln.t >= 0) {
                    row.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        const media = GetActiveMediaElement();
                        if (!media || !Number.isFinite(media.duration)) return;
                        try { media.currentTime = Math.max(0, ln.t); } catch (e) {}
                        this.sync(ln.t + 0.01);
                        try { revealPlayerControls(); } catch (e) {}
                    });
                }
                wrap.append(row);
            });
            el.append(wrap);
        },

        _applyActive() {
            const idx = this.activeIndex;
            this._mounts.forEach((m) => {
                if (!m.el || !m.el.isConnected) return;
                if (m.mode === 'static' || !this.synced) return;
                const view = this._viewFor(m.mode, m.view);

                // حالت خطی / کارت — فید نرم بین خطوط
                if (view === 'line' || view === 'card') {
                    const stage = m.el.querySelector('.ha-lrc-line-stage');
                    if (!stage) return;
                    const text = (idx >= 0 && this.lines[idx]) ? this.lines[idx].text : '';
                    const cur = stage.querySelector('.ha-lrc-fade.is-in');
                    const curText = cur ? (cur.textContent || '') : '';
                    if (cur && curText === text) return;

                    this._lineFadeToken = (this._lineFadeToken || 0) + 1;
                    const token = this._lineFadeToken;

                    // خروج خط قبلی
                    if (cur) {
                        cur.classList.remove('is-in');
                        cur.classList.add('is-out');
                        setTimeout(() => {
                            if (token !== this._lineFadeToken) return;
                            if (cur.parentNode) cur.parentNode.removeChild(cur);
                        }, 360);
                    }
                    // ورود خط جدید
                    const next = OA(document.createElement('div'), {
                        className: 'ha-lrc-fade',
                        innerHTML: text
                    });
                    stage.append(next);
                    // force reflow برای انیمیشن
                    void next.offsetWidth;
                    next.classList.add('is-in');
                    return;
                }

                // نرمال / فوکوس
                const rows = m.el.querySelectorAll('.ha-lrc-line');
                let activeEl = null;
                rows.forEach((row, i) => {
                    const isActive = i === idx;
                    const isNear = view === 'focus' && idx >= 0 && (i === idx - 1 || i === idx + 1);
                    row.classList.toggle('active', isActive);
                    row.classList.toggle('past', view === 'normal' && idx >= 0 && i < idx);
                    row.classList.toggle('is-near', isNear);
                    if (isActive) activeEl = row;
                });
                // اسکرول فقط داخل باکس — نه کل صفحه (scrollIntoView ممنوع)
                if (activeEl && (view === 'normal' || view === 'focus')) {
                    try {
                        LyricsEngine._scrollLineIntoBox(m.el, activeEl);
                    } catch (e) {}
                }
            });
        },

        /** اسکرول محلی خط فعال داخل container؛ از اسکرول صفحه جلوگیری می‌کند */
        _scrollLineIntoBox(box, lineEl) {
            if (!box || !lineEl) return;
            // مینی‌لیریک / overflow:hidden → اسکرول لازم نیست (فوکوس با show/hide کار می‌کند)
            if (box.classList.contains('mini-lyric') || (box.id === 'mini-lyric')) return;
            if (box.closest && box.closest('.mini-lyric')) return;

            let scroller = null;
            const candidates = [box, box.querySelector('.ha-lrc'), box.querySelector('.max-lyrics-body')];
            for (let i = 0; i < candidates.length; i++) {
                const el = candidates[i];
                if (!el) continue;
                const st = window.getComputedStyle(el);
                const oy = st.overflowY || st.overflow;
                if ((oy === 'auto' || oy === 'scroll') && el.scrollHeight > el.clientHeight + 2) {
                    scroller = el;
                    break;
                }
            }
            if (!scroller) {
                // بالا برو تا یک والد اسکرول‌پذیر داخل mount پیدا شود
                let p = lineEl.parentElement;
                while (p && p !== box.parentElement) {
                    const st = window.getComputedStyle(p);
                    const oy = st.overflowY || st.overflow;
                    if ((oy === 'auto' || oy === 'scroll') && p.scrollHeight > p.clientHeight + 2) {
                        scroller = p;
                        break;
                    }
                    if (p === box) break;
                    p = p.parentElement;
                }
            }
            if (!scroller) return;

            const boxRect = scroller.getBoundingClientRect();
            const lineRect = lineEl.getBoundingClientRect();
            const offset =
                (lineRect.top - boxRect.top) -
                (scroller.clientHeight / 2 - lineEl.offsetHeight / 2) +
                scroller.scrollTop;
            const max = Math.max(0, scroller.scrollHeight - scroller.clientHeight);
            const next = Math.max(0, Math.min(max, offset));
            if (Math.abs(scroller.scrollTop - next) < 2) return;
            try {
                scroller.scrollTo({ top: next, behavior: 'smooth' });
            } catch (e) {
                scroller.scrollTop = next;
            }
        }
    };


    function UpdateAllProgress() {
        const media = GetActiveMediaElement();
        if (!media) return;
        const P = ensurePlayer();
        const current = media.currentTime || 0;
        const duration = media.duration || 0;
        const percent = duration > 0 ? (current / duration) * 100 : 0;
    
        // موقع درگ seekbar مقدار را بازنویسی نکن (مخصوصاً موبایل)
        if (!P._seeking) {
            document.querySelectorAll('.video-inline-seek, .form-range').forEach((el) => {
                if (el === document.activeElement || el._haSeeking) return;
                el.value = percent;
                if (el.max != 100) el.max = 100;
            });
        }
        if (MinSkb && !P._seeking) MinSkb.style.width = percent + '%';
        if (MaxTim && MaxTim.length >= 1) MaxTim[0].innerHTML = FormatTime(current);
        if (MaxTim && MaxTim.length >= 2) MaxTim[1].innerHTML = FormatTime(duration);
        document.querySelectorAll('.video-inline-times .t-cur').forEach((el) => { el.innerHTML = FormatTime(current); });
        document.querySelectorAll('.video-inline-times .t-dur').forEach((el) => { el.innerHTML = FormatTime(duration); });
        document.querySelectorAll('.current-time').forEach((el) => { el.innerHTML = FormatTime(current); });
        document.querySelectorAll('.duration').forEach((el) => { el.innerHTML = FormatTime(duration); });
        // سینک لیریک با زمان پخش
        try { LyricsEngine.sync(current); } catch (e) {}
    }
    
    function setArtworkEl(container, thumbUrl) {
        if (!container) return;
        // imgهای قبلی را بردار (placeholder بماند)
        const imgs = container.querySelectorAll('img');
        for (let i = 0; i < imgs.length; i++) {
            imgs[i].remove();
        }
        if (thumbUrl) {
            container.append(
                OA(document.createElement('img'), { src: thumbUrl })
            );
        }
    }
    
    function UpdateNowPlayingMeta(item) {
        if (!item) return;
    
        const name = item.name || '';
        let artistName = '';
        if (item.artists && item.artists[0]) {
            const aid =
                typeof item.artists[0] === 'object'
                    ? item.artists[0].id
                    : item.artists[0];
            if (SN.artist && SN.artist[aid]) {
                artistName = SN.artist[aid].name || '';
            }
        }
    
        // artwork پلیر (مربعی/چرخش): tmb → tmv → cvr
        const srcThumb = resolvePlayerArtThumbUrl(item);
    
        setArtworkEl(MinArt, srcThumb);
        setArtworkEl(MaxArt, srcThumb);
        setArtworkEl(MaxDeskArt, srcThumb);
    
        if (MinTx1) MinTx1.innerHTML = name;
        if (MinTx2) MinTx2.innerHTML = artistName;
        if (MaxTx1) MaxTx1.innerHTML = name;
        if (MaxTx2) {
            const small = MaxTx2.querySelector('small');
            if (small) small.innerHTML = artistName;
            else MaxTx2.innerHTML = artistName;
        }
        if (MaxDeskTx1) MaxDeskTx1.innerHTML = name;
        if (MaxDeskTx2) MaxDeskTx2.innerHTML = artistName;
    
        // media-stage: تامب + عنوان + CRAL هنرمندان + CRAB about + پس‌زمینه
        const stageThumb = document.getElementById('media-stage-thumb');
        if (stageThumb) {
            if (srcThumb) {
                stageThumb.src = srcThumb;
                stageThumb.classList.remove('vis-none');
            } else {
                stageThumb.removeAttribute('src');
                stageThumb.classList.add('vis-none');
            }
        }
        const stageTitle = document.getElementById('media-stage-title');
        if (stageTitle){ 
            stageTitle.innerHTML = name;
            stageTitle.setAttribute('data-content',name);                 
        }

        const stageArtists = document.getElementById('media-stage-artists');
        if (stageArtists) {
            stageArtists.innerHTML = '';
            if (item.artists && item.artists.length) {
                stageArtists.append(CRAL(item.artists));
            }
        }

        const stageAbout = document.getElementById('media-stage-about');
        if (stageAbout) {
            stageAbout.innerHTML = '';
            if (item.meta && (item.meta.des || item.meta.rls || item.meta.ncp != null || item.meta.dur != null || item.meta.bd)) {
                stageAbout.append(CRAB(item.meta));
            }
        }

        const stageBg = document.getElementById('stage-bg');
        if (stageBg) {
            const bgUrl = resolveMediaStageBgUrl(item);
            if (bgUrl) {
                stageBg.style.backgroundImage = 'url(' + bgUrl + ')';
            } else {
                stageBg.style.backgroundImage = '';
            }
        }
    
        // لیریک برای stage + اهداف ثبت‌شده + mini-lyric
        try {
            LyricsEngine.mountDefaults(item);
            CRML(item);
            const stageLyr = document.getElementById('media-stage-lyric');
            if (stageLyr && LyricsEngine.lines.length) {
                // پیش‌فرض مخفی نباشد اگر کاربر قبلاً باز کرده
                if (typeof stageLyr.dataset.open === 'undefined') {
                    stageLyr.classList.remove('is-hidden');
                    stageLyr.dataset.open = '1';
                }
            }
        } catch (e) {}

        showPlayerChrome();
    }
    
    function applyPlayerMode(kind) {
        if (!Player) return;
        const stage = document.getElementById('media-stage');
        if (kind === 'video') {
            if (Video) Video.classList.remove('vis-none');
            if (stage) stage.classList.add('vis-none');
            const sub = document.getElementById('subtitle');
            const lyr = document.getElementById('lyric');
            if (sub) sub.classList.remove('vis-none');
            if (lyr) lyr.classList.add('vis-none');
        } else {
            if (Video) {
                Video.pause();
                if (Video.parentNode === Player) Video.classList.add('vis-none');
            }
            if (stage) stage.classList.remove('vis-none');
            const sub = document.getElementById('subtitle');
            const lyr = document.getElementById('lyric');
            if (sub) sub.classList.add('vis-none');
            if (lyr) lyr.classList.remove('vis-none');
        }
    }
    
    function sourceBit(s) {
        if (!s) return 0;
        if (typeof s.bit === 'number') return s.bit;
        if (typeof s.bit === 'string') return parseInt(s.bit, 10) || 0;
        return 0;
    }

    /** دمو فقط در دانلود؛ برای پلیر حذف می‌شود */
    function isDemoSource(s) {
        return !!(s && String(s.type || '').toLowerCase() === 'demo');
    }

    /** سورس‌های قابل پخش (بدون demo، با url) */
    function getPlayableSources(item) {
        if (!item || !Array.isArray(item.sources)) return [];
        return item.sources.filter((s) => s && s.url && !isDemoSource(s));
    }

    /**
     * برند دامنه از URL — مثلاً s1.cdnmrtehran.ir → cdnmrtehran
     * برای owner بی‌نام / id=0 در لیست دانلود
     */
    function hostLabelFromUrl(url) {
        try {
            let h = new URL(String(url)).hostname.toLowerCase().replace(/^www\./, '');
            const parts = h.split('.').filter(Boolean);
            if (!parts.length) return '';
            let i = 0;
            if (parts.length >= 3 && /^(s\d+|dl|cdn|static|media|mp3|video|m|files?)$/i.test(parts[0])) {
                i = 1;
            }
            if (parts.length - i >= 2) {
                return parts[parts.length - 2];
            }
            return parts[i] || parts[0];
        } catch (e) {
            return '';
        }
    }
    
    function pickSourceByQuality(sources, qualityPref) {
        if (!Array.isArray(sources) || !sources.length) return 0;
        const pref = qualityPref || 'medium';
        const indexed = sources.map((s, i) => ({ i, bit: sourceBit(s) }));
        const anyBit = indexed.some((x) => x.bit > 0);
        if (!anyBit) {
            if (pref === 'high') return 0;
            if (pref === 'low') return sources.length - 1;
            return Math.floor((sources.length - 1) / 2);
        }
        indexed.sort((a, b) => a.bit - b.bit);
        if (pref === 'low') return indexed[0].i;
        if (pref === 'high') return indexed[indexed.length - 1].i;
        const target = (indexed[0].bit + indexed[indexed.length - 1].bit) / 2;
        let best = indexed[0];
        let bestDiff = Math.abs(best.bit - target);
        for (let k = 1; k < indexed.length; k++) {
            const d = Math.abs(indexed[k].bit - target);
            if (d < bestDiff) { best = indexed[k]; bestDiff = d; }
        }
        return best.i;
    }

    /**
     * @param {number} index
     * @param {{ preserveTime?: boolean }} [options]
     * preserveTime=true → بعد از تعویض سورس همان ثانیه ادامه می‌دهد
     */
    function PlayMediaSourceAt(index, options) {
        const P = ensurePlayer();
        if (!P.nowPlaying) return;
        options = options || {};
    
        const sources = getPlayableSources(P.nowPlaying.item);
        const media = GetActiveMediaElement();
        if (!media) return;
    
        if (!sources.length || index < 0 || index >= sources.length) {
            console.warn('[HA][PlayMediaSourceAt] no valid source', { index, sourcesLen: sources.length });
            HandleAllSourcesFailed();
            return;
        }
    
        const src = sources[index];
        const gen = P.playGen;
        const bit = sourceBit(src);
        const srcName = src.name || src.label || src.title || (src.id != null ? ('id:' + src.id) : '');
    
        // تایم فعلی را قبل از عوض شدن src بگیر
        let resumeTime = 0;
        if (options.preserveTime) {
            const t = Number(media.currentTime);
            if (Number.isFinite(t) && t > 0.25) resumeTime = t;
        }
        const wantPlay = options.autoplay !== false; // پیش‌فرض: بعد از لود پخش شود
    
        setPlayerWaiting(true);
    
        P.nowPlaying.sourceIndex = index;
        P.nowPlaying.currentSource = src;
        P.nowPlaying.currentBit = bit;
        P.nowPlaying.currentUrl = src.url || '';
        P.nowPlaying._sourceFailed = false;
        P.nowPlaying.isBuffering = true;
        P.nowPlaying.isPlaying = false;
        P.nowPlaying._resumeTime = resumeTime;
    
        UpdateAllPlayButtons();
    
        console.log('[HA][PlayMediaSourceAt]', {
            sourceIndex: index,
            bit: bit,
            name: srcName || null,
            url: (src.url || '').slice(0, 80),
            resumeTime: resumeTime,
            preserveTime: !!options.preserveTime,
            gen: gen,
            id: P.nowPlaying.id,
            type: P.nowPlaying.type
        });
    
        if (!src.url) {
            console.warn('[HA][PlayMediaSourceAt] empty url → failure', { sourceIndex: index, bit, name: srcName });
            handleSourceFailure(index);
            return;
        }
    
        // listener یک‌بار برای seek بعد از metadata
        const onMeta = () => {
            media.removeEventListener('loadedmetadata', onMeta);
            if (gen !== P.playGen) return;
            const rt = P.nowPlaying && P.nowPlaying._resumeTime;
            if (rt > 0 && Number.isFinite(media.duration) && media.duration > 0) {
                const target = Math.min(rt, Math.max(0, media.duration - 0.35));
                try {
                    media.currentTime = target;
                    console.log('[HA][PlayMediaSourceAt] resumed at', target);
                } catch (e) {
                    console.warn('[HA][PlayMediaSourceAt] resume seek failed', e);
                }
            }
            if (P.nowPlaying) P.nowPlaying._resumeTime = 0;
        };
        media.addEventListener('loadedmetadata', onMeta);
    
        try { media.pause(); } catch (e) {}
        media.src = src.url;
        if (media.readyState >= 1) {
            try { onMeta(); } catch (e) {}
        }
    
        if (!wantPlay) return;
    
        const p = media.play();
        if (p !== undefined) {
            p.catch((err) => {
                // AbortError / NotAllowedError را به عنوان شکست سورس حساب نکن
                const name = err && err.name ? err.name : '';
                if (name === 'AbortError' || name === 'NotAllowedError') {
                    console.log('[HA][play().catch] ignored', { name, sourceIndex: index, gen, currentGen: P.playGen });
                    return;
                }
                if (gen !== P.playGen) {
                    console.log('[HA][play().catch] stale gen, ignore', { gen, current: P.playGen, sourceIndex: index });
                    return;
                }
                console.warn('[HA][play().catch] real error → source failure', {
                    errName: name,
                    message: err && err.message,
                    sourceIndex: index,
                    bit: bit,
                    name: srcName || null,
                    gen: gen
                });
                handleSourceFailure(index);
            });
        }
    }

    function PlayPreferredSource() {
        const P = ensurePlayer();
        if (!P.nowPlaying || !P.nowPlaying.item) return;
    
        const sources = getPlayableSources(P.nowPlaying.item);
        if (!sources.length) {
            HandleAllSourcesFailed();
            return;
        }
    
        const pref =
            P.nowPlaying.player === 'video'
                ? (GetSetting('videoQuality') || 'medium')
                : (GetSetting('audioQuality') || 'medium');
    
        PlayMediaSourceAt(pickSourceByQuality(sources, pref));
    }
    
    function handleSourceFailure(failedIndex) {
        const P = ensurePlayer();
        if (!P.nowPlaying) return;
    
        // جلوگیری از double-fire روی همان ایندکس
        if (P.nowPlaying.sourceIndex !== failedIndex || P.nowPlaying._sourceFailed) {
            console.log('[HA][handleSourceFailure] skip double', {
                failedIndex,
                currentIndex: P.nowPlaying.sourceIndex,
                alreadyFailed: !!P.nowPlaying._sourceFailed
            });
            return;
        }
        P.nowPlaying._sourceFailed = true;
    
        const sources = getPlayableSources(P.nowPlaying.item);
        const failedSrc = sources[failedIndex] || null;
        const bit = failedSrc ? sourceBit(failedSrc) : (P.nowPlaying.currentBit || 0);
        const srcName = failedSrc
            ? (failedSrc.name || failedSrc.label || failedSrc.title || (failedSrc.id != null ? ('id:' + failedSrc.id) : ''))
            : '';
    
        // ثبت سورس‌های امتحان‌شده تا بتوانیم به سورس‌های قبلی هم برگردیم
        if (!P.nowPlaying._triedSources) P.nowPlaying._triedSources = {};
        P.nowPlaying._triedSources[failedIndex] = true;
    
        console.warn('[HA][handleSourceFailure]', {
            sourceIndex: failedIndex,
            bit: bit,
            name: srcName || null,
            url: failedSrc && failedSrc.url ? String(failedSrc.url).slice(0, 80) : (P.nowPlaying.currentUrl || '').slice(0, 80),
            totalSources: sources.length,
            tried: Object.keys(P.nowPlaying._triedSources).map(Number),
            id: P.nowPlaying.id,
            type: P.nowPlaying.type,
            gen: P.playGen
        });
    
        // اول از failed+1 به بعد، بعد از 0 تا failed (سورس‌های قبلی هم امتحان می‌شوند)
        let next = -1;
        for (let i = failedIndex + 1; i < sources.length; i++) {
            if (!P.nowPlaying._triedSources[i]) { next = i; break; }
        }
        if (next < 0) {
            for (let i = 0; i < failedIndex; i++) {
                if (!P.nowPlaying._triedSources[i]) { next = i; break; }
            }
        }
    
        if (next >= 0) {
            console.log('[HA][handleSourceFailure] try another source', { nextIndex: next, total: sources.length });
            // _sourceFailed را برای ایندکس جدید آزاد می‌کنیم تا بتوان fail بعدی را هم ثبت کرد
            P.nowPlaying._sourceFailed = false;
            PlayMediaSourceAt(next);
            return;
        }
    
        console.warn('[HA][handleSourceFailure] all sources failed → HandleAllSourcesFailed');
        HandleAllSourcesFailed();
    }
    
    function HandleAllSourcesFailed() {
        const P = ensurePlayer();
        if (!P.nowPlaying) return;
    
        console.warn('[HA][HandleAllSourcesFailed]', {
            id: P.nowPlaying.id,
            type: P.nowPlaying.type,
            lastSourceIndex: P.nowPlaying.sourceIndex,
            skipAttempts: (P.skipAttempts || 0) + 1,
            queueLen: (P.queue || []).length,
            gen: P.playGen
        });
    
        setPlayerWaiting(false);
        P.nowPlaying.isPlaying = false;
        P.nowPlaying.isBuffering = false;
        UpdateAllPlayButtons();
    
        P.skipAttempts = (P.skipAttempts || 0) + 1;
        const max = P.queue.length || 1;
        if (P.skipAttempts >= max) {
            P.skipAttempts = 0;
            console.warn('[HA][HandleAllSourcesFailed] max skipAttempts reached, stop');
            return;
        }
        PlayNext(true);
    }
    
    async function playQueueAt(index) {
        const P = ensurePlayer();
        if (!P.queue.length || index < 0 || index >= P.queue.length) return;
    
        const row = P.queue[index];
        const gen = ++P.playGen; // توکن نسل برای جلوگیری از overwrite استیل
        setPlayerWaiting(true);
    
        console.log('[HA][playQueueAt] start', { index, id: row.id, type: row.type, gen });
    
        const item = await ensureQueueItemLoaded(row);
        if (gen !== P.playGen) {
            console.log('[HA][playQueueAt] stale after load, abort', { gen, current: P.playGen });
            return;
        }
        if (!item) {
            console.warn('[HA][playQueueAt] no item', row);
            setPlayerWaiting(false);
            PlayNext(true);
            return;
        }
    
        const kind = playerKind(row.type);
        // سوییچ مدیا: pause + clear src روی عنصر غیرفعال
        if (kind === 'video') {
            if (Audio) {
                try { Audio.pause(); } catch (e) {}
                try { Audio.removeAttribute('src'); Audio.load(); } catch (e) {}
            }
        } else {
            if (Video) {
                try { Video.pause(); } catch (e) {}
                try { Video.removeAttribute('src'); Video.load(); } catch (e) {}
            }
        }
    
        P.queueIndex = index;
        P._lastHistoryKey = null;
        P.nowPlaying = {
            id: row.id,
            type: row.type,
            player: kind,
            item: item,
            index: index,
            isPlaying: false,   // تا event play/playing
            isBuffering: true,
            sourceIndex: 0,
            playGen: gen
        };
    
        applyPlayerMode(kind);
        UpdateNowPlayingMeta(item);
        CRQT();
        UpdateAllPlayButtons();   // اینجا wlap می‌آید چون waiting=true
        if (gen !== P.playGen) {
            console.log('[HA][playQueueAt] stale before PlayPreferredSource', { gen, current: P.playGen });
            return;
        }
        PlayPreferredSource();    // داخلش src + play؛ waiting با event playing خاموش می‌شود
    }

    function isNowPlayingId(id, playerKind) {
        const P = ensurePlayer();
        const np = P.nowPlaying;
        if (!np) return false;
        if (Number(np.id) !== Number(id)) return false;
        if (playerKind && np.player !== playerKind) return false;
        return true;
    }
    
    function TogglePlayPause() {
        const P = ensurePlayer();
        if (P.waiting) return;
        if (!P.nowPlaying) return;
        const media = GetActiveMediaElement();
        if (!media) return;
        if (!media.paused) { media.pause(); return; }
        const hasSrc = !!(media.currentSrc || media.getAttribute('src'));
        if (!hasSrc) {
            PlayMediaSourceAt(P.nowPlaying.sourceIndex || 0);
            return;
        }
        const gen = P.playGen;
        const p = media.play();
        if (p !== undefined) {
            p.catch((err) => {
                const name = err && err.name ? err.name : '';
                if (name === 'AbortError' || name === 'NotAllowedError') {
                    console.log('[HA][TogglePlayPause play().catch] ignored', { name, gen });
                    return;
                }
                if (gen !== P.playGen) return;
                console.warn('[HA][TogglePlayPause play().catch]', { name, message: err && err.message, gen });
            });
        }
    }
    
    function getNextQueueIndex(fromError) {
        const P = ensurePlayer();
        const q = P.queue || [];
        if (!q.length) return -1;
        const cur = typeof P.queueIndex === 'number' ? P.queueIndex : 0;
        const mode = P.repeat || 'off';
        const shuffle = !!P.shuffle;
    
        if (!shuffle) {
            let next = cur + 1;
            if (next >= q.length) return mode === 'all' ? 0 : -1;
            return next;
        }
    
        const currentRow = q[cur];
        if (currentRow) markPlayed(currentRow);
    
        let remaining = [];
        for (let i = 0; i < q.length; i++) {
            if (i === cur && !fromError) continue;
            if (!P.playedInShuffle[queueItemKey(q[i])]) remaining.push(i);
        }
        if (!remaining.length) {
            if (mode === 'all') {
                resetShuffleMemory();
                remaining = [];
                for (let i = 0; i < q.length; i++) {
                    if (i !== cur) remaining.push(i);
                }
                if (!remaining.length) return cur;
            } else return -1;
        }
        return remaining[Math.floor(Math.random() * remaining.length)];
    }
    
    function PlayNext(fromError) {
        const P = ensurePlayer();
        if (!P.queue.length || !P.nowPlaying) return;
        if (!fromError && P.repeat === 'one') {
            const media = GetActiveMediaElement();
            if (media) {
                media.currentTime = 0;
                const p = media.play();
                if (p) p.catch(() => {});
            }
            return;
        }
        const next = getNextQueueIndex(!!fromError);
        if (next < 0) {
            P.nowPlaying.isPlaying = false;
            UpdateAllPlayButtons();
            return;
        }
        playQueueAt(next);
    }
    
    function getPrevQueueIndex() {
        const P = ensurePlayer();
        const q = P.queue || [];
        if (!q.length) return -1;
        const cur = typeof P.queueIndex === 'number' ? P.queueIndex : 0;
        const mode = P.repeat || 'off';
        const shuffle = !!P.shuffle;
    
        if (!shuffle) {
            let prev = cur - 1;
            if (prev < 0) return mode === 'all' ? q.length - 1 : -1;
            return prev;
        }
    
        // شافل: از بین آیتم‌های قبلاً پخش‌شده یکی را تصادفی برگردان (به‌جز جاری)
        const played = [];
        for (let i = 0; i < q.length; i++) {
            if (i === cur) continue;
            if (P.playedInShuffle[queueItemKey(q[i])]) played.push(i);
        }
        if (played.length) {
            return played[Math.floor(Math.random() * played.length)];
        }
        // هنوز چیزی پخش نشده → مثل حالت عادی به قبلی برو
        let prev = cur - 1;
        if (prev < 0) return mode === 'all' ? q.length - 1 : -1;
        return prev;
    }
    
    function PlayPrevious() {
        const P = ensurePlayer();
        if (!P.nowPlaying) return;
        const media = GetActiveMediaElement();
        if (media && media.currentTime > 3) {
            media.currentTime = 0;
            const p = media.play();
            if (p) p.catch(() => {});
            return;
        }
        const prev = getPrevQueueIndex();
        if (prev < 0) {
            console.log('[HA][PlayPrevious] no previous', { queueIndex: P.queueIndex, repeat: P.repeat, shuffle: P.shuffle });
            return;
        }
        console.log('[HA][PlayPrevious]', { from: P.queueIndex, to: prev, shuffle: !!P.shuffle, repeat: P.repeat });
        playQueueAt(prev);
    }
    
    function cycleRepeatMode() {
        const P = ensurePlayer();
        const order = ['off', 'one', 'all'];
        P.repeat = order[(order.indexOf(P.repeat || 'off') + 1) % 3];
        UpdateRepeatShuffleUI();
        return P.repeat;
    }
    
    function toggleShuffle() {
        const P = ensurePlayer();
        P.shuffle = !P.shuffle;
        P.playedInShuffle = {};
        if (P.shuffle && P.queue[P.queueIndex]) {
            P.playedInShuffle[queueItemKey(P.queue[P.queueIndex])] = true;
        }
        UpdateRepeatShuffleUI();
        return P.shuffle;
    }
    
    function UpdateRepeatShuffleUI() {
        const P = ensurePlayer();
        const mode = P.repeat || 'off';   // off | one | all
        const shuffleOn = !!P.shuffle;
    
        const repeatIcon =
            mode === 'one' ? svg_rpt1 :
            mode === 'all' ? svg_rpta :
            svg_rpt;
    
        const shuffleIcon = shuffleOn ? svg_shfx : svg_shfo;
    
        const repeatEls = [];
        if (MaxMobBtn && MaxMobBtn[0]) repeatEls.push(MaxMobBtn[0]);
        if (MaxDeskBtns && MaxDeskBtns[0]) repeatEls.push(MaxDeskBtns[0]);
        document.querySelectorAll('#repeat, .repeat').forEach((el) => {
            if (repeatEls.indexOf(el) === -1) repeatEls.push(el);
        });
    
        repeatEls.forEach((el) => {
            el.innerHTML = repeatIcon;
            el.classList.remove('active', 'repeat-off', 'repeat-one', 'repeat-all');
            el.classList.add('repeat-' + mode);
            if (mode !== 'off') el.classList.add('active');
            el.title =
                mode === 'one' ? 'Repeat one' :
                mode === 'all' ? 'Repeat all' :
                'Repeat off';
        });
    
        const shuffleEls = [];
        if (MaxMobBtn && MaxMobBtn[4]) shuffleEls.push(MaxMobBtn[4]);
        if (MaxDeskBtns && MaxDeskBtns[4]) shuffleEls.push(MaxDeskBtns[4]);
        document.querySelectorAll('#shuffle, .btn-shuffle').forEach((el) => {
            if (shuffleEls.indexOf(el) === -1) shuffleEls.push(el);
        });
    
        shuffleEls.forEach((el) => {
            el.innerHTML = shuffleIcon;
            el.classList.toggle('active', shuffleOn);
            el.classList.toggle('shuffle-on', shuffleOn);
            el.classList.toggle('shuffle-off', !shuffleOn);
            el.title = shuffleOn ? 'Shuffle on' : 'Shuffle off';
        });
    }
    
    function setArtSpinning(playing) {
        [MinArt, MaxArt, MaxDeskArt].forEach((el) => {
            if (!el) return;
            el.classList.toggle('is-playing', !!playing);
        });
        // media-stage header spin (اگر setting فعال باشد)
        const stageThumbWrap = document.querySelector('#media-stage .thumbail');
        if (stageThumbWrap && stageThumbWrap.classList.contains('player-art-spin')) {
            stageThumbWrap.classList.toggle('is-playing', !!playing);
        }
    }
    
    function setPlayerWaiting(on) {
        const P = ensurePlayer();
        P.waiting = !!on;
        // جلوگیری از گیر کردن ابدی waiting (بلاک TogglePlayPause)
        if (P._waitingTimer) {
            clearTimeout(P._waitingTimer);
            P._waitingTimer = null;
        }
        if (on) {
            const genAtStart = P.playGen;
            P._waitingTimer = setTimeout(() => {
                P._waitingTimer = null;
                if (!P.waiting) return;
                if (P.playGen !== genAtStart) return; // نسل عوض شده
                console.warn('[HA][waiting-timeout] force clear after 20s', {
                    gen: P.playGen,
                    id: P.nowPlaying && P.nowPlaying.id,
                    sourceIndex: P.nowPlaying && P.nowPlaying.sourceIndex
                });
                P.waiting = false;
                if (P.nowPlaying) {
                    P.nowPlaying.isBuffering = false;
                }
                UpdateAllPlayButtons();
            }, 20000);
        }
        UpdateAllPlayButtons();
    }
    
    function bindMediaElement(media) {
        if (!media || media._haBound) return;
        media._haBound = true;
        media.addEventListener('timeupdate', UpdateAllProgress);
        media.addEventListener('loadedmetadata', UpdateAllProgress);
        media.addEventListener('play', () => {
            if (GetActiveMediaElement() !== media) return;
            const P = ensurePlayer();
            if (!P.nowPlaying) return;
            P.nowPlaying.isPlaying = true;
            UpdateAllPlayButtons();
        });
        media.addEventListener('playing', () => {
            if (GetActiveMediaElement() !== media) return;
            const P = ensurePlayer();
            if (!P.nowPlaying) return;
            P.nowPlaying.isBuffering = false;
            P.skipAttempts = 0;
            showPlayerChrome();
            UpdateAllPlayButtons();
            setArtSpinning(true);
            setPlayerWaiting(false);
            const row = P.queue[P.queueIndex];
            if (row) {
                markPlayed(row); // فقط شفل — همان قبلی
                recordPlayHistory(row); // تاریخچه پروفایل
            }
            console.log('[HA][media:playing]', {
                id: P.nowPlaying.id,
                type: P.nowPlaying.type,
                sourceIndex: P.nowPlaying.sourceIndex,
                bit: P.nowPlaying.currentBit,
                gen: P.playGen
            });
        });
        media.addEventListener('waiting', () => {
            if (GetActiveMediaElement() !== media) return;
            const P = ensurePlayer();
            if (!P.nowPlaying) return;
            P.nowPlaying.isBuffering = true;
            setArtSpinning(false);
            UpdateAllPlayButtons();
        });
        media.addEventListener('pause', () => {
            if (GetActiveMediaElement() !== media) return;
            const P = ensurePlayer();
            if (!P.nowPlaying) return;
            P.nowPlaying.isPlaying = false;
            P.nowPlaying.isBuffering = false;
            UpdateAllPlayButtons();
            setArtSpinning(false);
        });
        media.addEventListener('ended', () => {
            if (GetActiveMediaElement() !== media) {
                console.log('[HA][media:ended] ignored on inactive element');
                return;
            }
            setArtSpinning(false);
            PlayNext(false);
        });
        media.addEventListener('error', () => {
            if (GetActiveMediaElement() !== media) {
                console.log('[HA][media:error] ignored on inactive element');
                return;
            }
            const P = ensurePlayer();
            if (!P.nowPlaying) return;
            const idx = P.nowPlaying.sourceIndex || 0;
            const sources = getPlayableSources(P.nowPlaying.item);
            const src = sources[idx] || null;
            const bit = src ? sourceBit(src) : (P.nowPlaying.currentBit || 0);
            const srcName = src
                ? (src.name || src.label || src.title || (src.id != null ? ('id:' + src.id) : ''))
                : '';
            const mediaErr = media.error;
            console.warn('[HA][media:error]', {
                sourceIndex: idx,
                bit: bit,
                name: srcName || null,
                url: (src && src.url ? String(src.url) : (P.nowPlaying.currentUrl || '')).slice(0, 80),
                mediaErrorCode: mediaErr ? mediaErr.code : null,
                mediaErrorMessage: mediaErr ? mediaErr.message : null,
                id: P.nowPlaying.id,
                type: P.nowPlaying.type,
                gen: P.playGen
            });
            setPlayerWaiting(true);
            UpdateAllPlayButtons();
            setArtSpinning(false);
            handleSourceFailure(idx);
        });
        media.addEventListener('seeked', () => {
            if (GetActiveMediaElement() !== media) return;
            const P = ensurePlayer();
            if (!P.nowPlaying) return;
            P.nowPlaying.isBuffering = false;
            UpdateAllPlayButtons();
            if (!media.paused) setArtSpinning(true);
        });
        
        media.addEventListener('seeking', () => {
            if (GetActiveMediaElement() !== media) return;
            const P = ensurePlayer();
            if (!P.nowPlaying) return;
            P.nowPlaying.isBuffering = true;
            UpdateAllPlayButtons();
            setArtSpinning(false);
        });
    }
    
    function wireOnce(el, event, handler) {
        if (!el || el['_ha_' + event]) return;
        el['_ha_' + event] = true;
        el.addEventListener(event, handler);
    }
    
    // shell UI (بدون تغییر منطق قبلی)
    function showPlayerChrome() {
        if (MinPly && !(MaxPly && MaxPly.classList.contains('translate-to-up'))) {
            MinPly.classList.remove('translate-to-down', 'vis-none');
            MinPly.classList.add('translate-to-up');
        }
        if (MaxDeskPly) {
            MaxDeskPly.classList.remove('translate-to-down', 'vis-none');
            MaxDeskPly.classList.add('is-active', 'translate-to-up');
        }
    }
    
    function MinMiz(e) {
        if (!e) return;
        e.classList.remove('translate-to-up');
        e.classList.add('translate-to-down');
    }
    function MaxMiz(e) {
        if (!e) return;
        e.classList.remove('translate-to-down', 'vis-none');
        e.classList.add('translate-to-up');
    }

    /**
     * CRML — ساخت / به‌روزرسانی mini-lyric کنار maximum-player-mobile
     * فقط اگر آیتم lyrics داشته باشد نمایش داده می‌شود
     */
    function CRML(item) {
        const box = document.getElementById('mini-lyric');
        if (!box) return;
        item = item || (ensurePlayer().nowPlaying && ensurePlayer().nowPlaying.item);
        if (!item || !LyricsEngine.hasLyrics(item)) {
            box.classList.add('vis-none');
            box.innerHTML = '';
            try { LyricsEngine.unmount(box); } catch (e) {}
            return;
        }
        LyricsEngine.loadFromItem(item);
        // فوکوس برای مینی — حتی اگر سینک نباشد لیست فشرده
        const mode = LyricsEngine.synced ? 'sync' : 'static';
        const view = LyricsEngine.synced ? 'focus' : 'static';
        box.classList.remove('vis-none');
        LyricsEngine.mount(box, mode, view);
        if (!box._haLyrClick) {
            box._haLyrClick = true;
            box.addEventListener('click', () => openMaxLyricsPanel());
        }
        // عنوان پنل بزرگ
        const titleEl = document.getElementById('max-lyrics-title');
        if (titleEl) titleEl.innerHTML = item.name || '';
        // body پنل بزرگ را هم آماده کن (بدون باز کردن)
        const body = document.getElementById('max-lyrics-body');
        if (body) {
            LyricsEngine.mount(body, mode, LyricsEngine.synced ? 'normal' : 'static');
        }
    }

    function isMaxLyricsOpen() {
        const panel = document.getElementById('maximum-player-lyrics');
        return !!(panel && panel.classList.contains('is-open'));
    }

    /** فقط پنل لیریک — CRQT نه؛ اگر max از قبل برای کیو باز نبود با بستن لیریک دوباره پایین می‌رود */
    function openMaxLyricsPanel() {
        const P = ensurePlayer();
        const item = P.nowPlaying && P.nowPlaying.item;
        if (!item || !LyricsEngine.hasLyrics(item)) return;
        CRML(item);
        try {
            // آیا قبل از لیریک، max برای پلی‌لیست/کیو عمداً باز بود؟
            const maxUp = !!(MaxPly && MaxPly.classList.contains('translate-to-up'));
            const onlyLyricsBefore = !!(MaxPly && MaxPly.classList.contains('ha-lyrics-only'));
            if (MaxPly) {
                MaxPly._haQueueContext = maxUp && !onlyLyricsBefore;
                MaxPly.classList.add('ha-lyrics-only');
            }
            MaxMiz(MaxPly);
            MinMiz(MinPly);
        } catch (e) {}
        const panel = document.getElementById('maximum-player-lyrics');
        if (!panel) return;
        panel.classList.remove('vis-none');
        panel.classList.add('is-open');
        const titleEl = document.getElementById('max-lyrics-title');
        if (titleEl) titleEl.innerHTML = item.name || '';
        const body = document.getElementById('max-lyrics-body');
        if (body) {
            const mode = LyricsEngine.synced ? 'sync' : 'static';
            LyricsEngine.mount(body, mode, LyricsEngine.synced ? 'normal' : 'static');
            try {
                const media = GetActiveMediaElement();
                LyricsEngine.sync(media ? (media.currentTime || 0) : 0);
            } catch (e) {}
        }
    }

    /**
     * PlayList: فقط کیو را لانچ کن.
     * اگر لیریک باز است اول فقط لیریک مینیمایز شود، بعد کیو دیده شود.
     */
    function openMaxQueuePanel() {
        if (isMaxLyricsOpen()) {
            // بستن لیریک بدون پایین آوردن max — چون خودمان کیو را باز می‌کنیم
            const panel = document.getElementById('maximum-player-lyrics');
            if (panel) {
                panel.classList.remove('is-open');
                panel.classList.add('vis-none');
            }
            if (MaxPly) {
                MaxPly._haQueueContext = true;
                MaxPly.classList.remove('ha-lyrics-only');
            }
        }
        try {
            if (MaxPly) {
                MaxPly._haQueueContext = true;
                MaxPly.classList.remove('ha-lyrics-only');
            }
            MaxMiz(MaxPly);
            MinMiz(MinPly);
            CRQT();
            const item = ensurePlayer().nowPlaying && ensurePlayer().nowPlaying.item;
            if (item) CRML(item);
        } catch (e) {}
    }

    /**
     * مینیمایز فقط لیریک.
     * اگر max فقط برای لیریک باز شده بود → شل max هم پایین می‌رود (کیو لانچ نمی‌ماند).
     * اگر قبلش پلی‌لیست باز بود → فقط لیریک بسته می‌شود و کیو می‌ماند.
     */
    function closeMaxLyricsPanel() {
        const panel = document.getElementById('maximum-player-lyrics');
        if (!panel) return;
        panel.classList.remove('is-open');
        panel.classList.add('vis-none');
        const keepQueue = !!(MaxPly && MaxPly._haQueueContext);
        if (MaxPly) MaxPly.classList.remove('ha-lyrics-only');
        if (!keepQueue) {
            MinMiz(MaxPly);
            MaxMiz(MinPly);
        }
        if (MaxPly) MaxPly._haQueueContext = false;
    }

    /** مینیمایز هدر کیو / max */
    function minimizeMaxToMini() {
        if (MaxPly) {
            MaxPly.classList.remove('ha-lyrics-only');
            MaxPly._haQueueContext = false;
        }
        const panel = document.getElementById('maximum-player-lyrics');
        if (panel) {
            panel.classList.remove('is-open');
            panel.classList.add('vis-none');
        }
        MinMiz(MaxPly);
        MaxMiz(MinPly);
    }

    function openMaxFromMini() {
        if (MaxPly) {
            MaxPly.classList.remove('ha-lyrics-only');
            MaxPly._haQueueContext = true;
        }
        const panel = document.getElementById('maximum-player-lyrics');
        if (panel) {
            panel.classList.remove('is-open');
            panel.classList.add('vis-none');
        }
        MaxMiz(MaxPly);
        MinMiz(MinPly);
        CRQT();
        try {
            const item = ensurePlayer().nowPlaying && ensurePlayer().nowPlaying.item;
            if (item) CRML(item);
        } catch (e) {}
    }
    function closePlaybackChrome() {
        if (MinPly) {
            MinPly.classList.remove('translate-to-up');
            MinPly.classList.add('translate-to-down');
        }
        if (MaxPly) {
            MaxPly.classList.remove('translate-to-up');
            MaxPly.classList.add('translate-to-down');
        }
        if (MaxDeskPly) {
            MaxDeskPly.classList.add('vis-none');
            MaxDeskPly.classList.remove('is-active');
        }
        if (Audio) Audio.pause();
        if (Video) Video.pause();
    }
    
    function InitMediaPlayers() {
        bindMediaElement(Audio);
        bindMediaElement(Video);
        
    
        const transportSelector = [
            '#play',
            '.maximum-player-desktop .play-pause',
            '.maximum-player-mobile .play-pause',
            '#player-box #play',
            '#player-box .play-pause'
        ].join(', ');
        document.querySelectorAll(transportSelector).forEach((btn) => {
            wireOnce(btn, 'click', (ev) => {
                ev.preventDefault();
                ev.stopPropagation();
                TogglePlayPause();
            });
        });
        document.querySelectorAll('.play-next, #next').forEach((btn) => {
            wireOnce(btn, 'click', () => PlayNext(false));
        });
        document.querySelectorAll('.play-prev, #prev').forEach((btn) => {
            wireOnce(btn, 'click', () => PlayPrevious());
        });
        document.querySelectorAll('#repeat, .repeat').forEach((el) => {
            wireOnce(el, 'click', () => cycleRepeatMode());
        });
        document.querySelectorAll('#shuffle, .btn-shuffle').forEach((el) => {
            wireOnce(el, 'click', () => toggleShuffle());
        });
        const fsBtn = document.getElementById('fullscreen');
        if (fsBtn) wireOnce(fsBtn, 'click', () => openPlayerShell());
        
        const backBtn = document.getElementById('BackToHome');
        if (backBtn) wireOnce(backBtn, 'click', () => closePlayerShell());
    
        if (!document._haSeekInput) {
            document._haSeekInput = true;

            const isSeekEl = (t) =>
                t &&
                t.classList &&
                (t.classList.contains('video-inline-seek') || t.classList.contains('form-range'));

            const beginSeek = (ev) => {
                const t = ev.target;
                if (!isSeekEl(t)) return;
                const P = ensurePlayer();
                P._seeking = true;
                t._haSeeking = true;
                revealPlayerControls();
            };
            const endSeek = (ev) => {
                const t = ev.target;
                if (!isSeekEl(t) && !(ensurePlayer()._seeking)) return;
                const P = ensurePlayer();
                P._seeking = false;
                if (t && t.classList) t._haSeeking = false;
                document.querySelectorAll('.video-inline-seek, .form-range').forEach((el) => {
                    el._haSeeking = false;
                });
            };
            const applySeek = (ev) => {
                const t = ev.target;
                if (!isSeekEl(t)) return;
                const media = GetActiveMediaElement();
                if (!media || !media.duration || media.error) return;
                if (!(media.currentSrc || media.getAttribute('src'))) return;
                const ratio = parseFloat(t.value);
                if (!Number.isFinite(ratio)) return;
                try {
                    media.currentTime = (ratio / 100) * media.duration;
                } catch (e) {}
                // نمایش زمان در حین درگ
                document.querySelectorAll('.current-time').forEach((el) => {
                    el.innerHTML = FormatTime(media.currentTime || 0);
                });
                if (MinSkb) MinSkb.style.width = ratio + '%';
            };

            document.addEventListener('pointerdown', beginSeek, true);
            document.addEventListener('touchstart', beginSeek, { capture: true, passive: true });
            document.addEventListener('mousedown', beginSeek, true);
            document.addEventListener('pointerup', endSeek, true);
            document.addEventListener('touchend', endSeek, true);
            document.addEventListener('mouseup', endSeek, true);
            document.addEventListener('input', applySeek);
            document.addEventListener('change', applySeek);
        }
    
        if (MinArt) wireOnce(MinArt, 'click', openMaxFromMini);
        if (MinTxs) wireOnce(MinTxs, 'click', openMaxFromMini);
        if (MinPlyBtn && MinPlyBtn.length >= 4) {
            wireOnce(MinPlyBtn[0], 'click', () => PlayPrevious());
            wireOnce(MinPlyBtn[1], 'click', (ev) => {
                ev.preventDefault();
                ev.stopPropagation();
                TogglePlayPause();
            });
            wireOnce(MinPlyBtn[2], 'click', () => PlayNext(false));
            wireOnce(MinPlyBtn[3], 'click', () => closePlaybackChrome());
        }
        // مینیمایز هدر کیو → فقط max/queue (لیریک را نمی‌بندد)
        if (MaxQueHdrBtns && MaxQueHdrBtns.length >= 1) {
            wireOnce(MaxQueHdrBtns[0], 'click', () => minimizeMaxToMini());
        }

        // مینیمایز هدر لیریک → فقط خود پنل لیریک
        const maxLyrMin = document.getElementById('max-lyrics-minimize');
        if (maxLyrMin) wireOnce(maxLyrMin, 'click', () => closeMaxLyricsPanel());

        // موبایل: ردیف دوم buttons.m-top-1 — دکمه وسط/سوم → فقط لیریک
        if (MaxMobPly) {
            const rows = MaxMobPly.querySelectorAll('.buttons.m-top-1');
            if (rows.length >= 2) {
                const btns = rows[1].querySelectorAll('.button');
                const lyrBtn = btns[2] || btns[1];
                if (lyrBtn) wireOnce(lyrBtn, 'click', () => openMaxLyricsPanel());
            }
        }

        if (MaxDeskBtns && MaxDeskBtns.length >= 5) {
            wireOnce(MaxDeskBtns[0], 'click', () => cycleRepeatMode());
            wireOnce(MaxDeskBtns[1], 'click', () => PlayPrevious());
            wireOnce(MaxDeskBtns[3], 'click', () => PlayNext(false));
            wireOnce(MaxDeskBtns[4], 'click', () => toggleShuffle());
        }

        // دسکتاپ details — مستقل؛ PlayList فقط اگر لیریک باز باشد اول آن را می‌بندد
        if (MaxDeskDtls && MaxDeskDtls.length >= 1) {
            wireOnce(MaxDeskDtls[0], 'click', () => openMaxQueuePanel());
            if (MaxDeskDtls[1]) wireOnce(MaxDeskDtls[1], 'click', () => openMaxLyricsPanel());
            if (MaxDeskDtls[2]) wireOnce(MaxDeskDtls[2], 'click', () => openPlayerShell());
            if (MaxDeskDtls[3]) wireOnce(MaxDeskDtls[3], 'click', () => closePlaybackChrome());
        }
        if (MaxMobMin) wireOnce(MaxMobMin, 'click', () => minimizeMaxToMini());
        if (MaxMobBtn && MaxMobBtn.length >= 5) {
            wireOnce(MaxMobBtn[0], 'click', () => cycleRepeatMode());
            wireOnce(MaxMobBtn[1], 'click', () => PlayPrevious());
            wireOnce(MaxMobBtn[3], 'click', () => PlayNext(false));
            wireOnce(MaxMobBtn[4], 'click', () => toggleShuffle());
        }
        if (MaxQue && !MaxQue._haNav) {
            MaxQue._haNav = true;
            MaxQue.addEventListener('click', (ev) => {
                let a = ev.target;
                while (a && a !== MaxQue) {
                    if (a.tagName === 'A' && a.getAttribute('href')) break;
                    a = a.parentNode;
                }
                if (!a || a === MaxQue) return;
                const href = a.getAttribute('href');
                if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
                if (a.host && a.host !== location.host) return;
                ev.preventDefault();
                ev.stopPropagation();
                minimizeMaxToMini();
                chp(href);
            });
        }
        UpdateRepeatShuffleUI();
    }
    
    CRPL();
    InitMediaPlayers();

    function CRTM(t){
        
        const tms = OA(document.createElement("div"), {className: "toast-message show"});
        const spn = OA(document.createElement("span"), {innerHTML:t});
        tms.append(spn);
        document.body.append(tms);
        setTimeout(() => {
            tms.className = tms.className.replace("show", "");
            tms.remove();
          }, 3000);
    }


}();  

</script>
</body>
</html>
<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>