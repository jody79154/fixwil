<?php
// Include on pages inside student/ after student authentication.
?>
<link rel="stylesheet" href="../css/wil_chatbot.css">
<div id="wil-chat" class="wil-chat" data-endpoint="chatbot.php">
  <button id="wil-chat-toggle" class="wil-chat-toggle" type="button" aria-controls="wil-chat-panel" aria-expanded="false" aria-label="Open WIL Help">Chat with WIL Help</button>
  <section id="wil-chat-panel" class="wil-chat-panel" hidden aria-label="WIL Connect Helper">
    <header class="wil-chat-header"><div><strong>WIL Connect Helper</strong><small>Application assistance • No AI API</small></div><button id="wil-chat-close" type="button" aria-label="Close chatbot">×</button></header>
    <div id="wil-chat-history" class="wil-chat-history" role="log" aria-live="polite"><p class="wil-chat-bot">Hi! I can help you find WIL jobs and apply. What would you like to know?</p></div>
    <div id="wil-chat-suggestions" class="wil-chat-suggestions">
      <button type="button" data-question="How do I apply for a job?">How to apply</button>
      <button type="button" data-question="What CV file can I upload?">CV upload</button>
      <button type="button" data-question="Where do I find jobs?">Find jobs</button>
      <button type="button" data-question="How do I check application status?">Application status</button>
    </div>
    <form id="wil-chat-form" class="wil-chat-form"><label class="wil-chat-sr" for="wil-chat-input">Your question</label><input id="wil-chat-input" maxlength="1200" required placeholder="Ask a question..." autocomplete="off"><button type="submit">Send</button></form>
  </section>
</div>
<script defer src="../js/wil_chatbot.js"></script>
