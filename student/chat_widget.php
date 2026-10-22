<?php
// Reusable public and student widget. Set $wilChatBase = '' on root-level pages.
$wilChatBase = isset($wilChatBase) ? $wilChatBase : '../';
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($wilChatBase, ENT_QUOTES); ?>css/wil_chatbot.css">
<div id="wil-chat" class="wil-chat" data-endpoint="<?php echo htmlspecialchars($wilChatBase, ENT_QUOTES); ?>chatbot.php">
  <button id="wil-chat-toggle" class="wil-chat-toggle" type="button" aria-controls="wil-chat-panel" aria-expanded="false" aria-label="Open WIL Help">Chat with WIL Help</button>
  <section id="wil-chat-panel" class="wil-chat-panel" hidden aria-label="WIL Connect Helper">
    <header class="wil-chat-header"><div><strong>WIL Connect Helper</strong><small>Application assistance • No AI API</small></div><button id="wil-chat-close" type="button" aria-label="Close chatbot">×</button></header>
    <div id="wil-chat-history" class="wil-chat-history" role="log" aria-live="polite"><p class="wil-chat-bot">Hi! I can help you register, verify your email, add badges to your profile and apply for WIL jobs.</p></div>
    <div id="wil-chat-suggestions" class="wil-chat-suggestions">
      <button type="button" data-question="How do I register as a student?">Student registration</button>
      <button type="button" data-question="Which badges are supported?">Supported badges</button>
      <button type="button" data-question="How do I add a badge?">Add a badge</button>
      <button type="button" data-question="How do I apply for a job?">How to apply</button>
    </div>
    <form id="wil-chat-form" class="wil-chat-form"><label class="wil-chat-sr" for="wil-chat-input">Your question</label><input id="wil-chat-input" maxlength="1200" required placeholder="Ask a question..." autocomplete="off"><button type="submit">Send</button></form>
  </section>
</div>
<script defer src="<?php echo htmlspecialchars($wilChatBase, ENT_QUOTES); ?>js/wil_chatbot.js"></script>
