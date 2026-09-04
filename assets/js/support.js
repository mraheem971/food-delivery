/**
 * FoodHub Express - Customer Support & AI Assistant Widget
 */

document.addEventListener('DOMContentLoaded', () => {
    initSupportAssistant();
});

function initSupportAssistant() {
    const floatingBtn = document.getElementById('floatingSupportBtn');
    const widgetPanel = document.getElementById('supportWidgetPanel');
    const closeBtn = document.getElementById('closeSupportWidgetBtn');
    const form = document.getElementById('supportChatForm');
    const input = document.getElementById('supportChatInput');
    const msgContainer = document.getElementById('supportMessagesContainer');

    if (!floatingBtn || !widgetPanel) return;

    floatingBtn.addEventListener('click', () => {
        widgetPanel.classList.toggle('active');
        if (widgetPanel.classList.contains('active') && input) {
            input.focus();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            widgetPanel.classList.remove('active');
        });
    }

    if (form && input && msgContainer) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            // Append User message
            appendChatMessage(text, 'user');
            input.value = '';

            // Query API
            fetch(`api/support.php?query=${encodeURIComponent(text)}`)
                .then(res => res.json())
                .then(data => {
                    const reply = data.reply || "I am here to assist you with order delivery, deals, dietary guides, or refund requests!";
                    setTimeout(() => {
                        appendChatMessage(reply, 'bot');
                    }, 400);
                })
                .catch(() => {
                    appendChatMessage("Sorry, I'm having trouble connecting right now. Please try again or call support!", 'bot');
                });
        });
    }

    function appendChatMessage(msg, sender = 'bot') {
        const div = document.createElement('div');
        div.className = sender === 'user' ? 'user-msg' : 'bot-msg';
        div.textContent = msg;
        msgContainer.appendChild(div);
        msgContainer.scrollTop = msgContainer.scrollHeight;
    }
}
