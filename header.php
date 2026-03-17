<!-- Chatbot Widget -->
<div id="chatbot-container">
    <!-- Chat Toggle Button -->
    <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Open chat">
        <span class="chat-icon">💬</span>
        <span class="close-icon">✕</span>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="chatbot-window">
        <div class="chatbot-header">
            <div class="chatbot-avatar">🤖</div>
            <div class="chatbot-info">
                <h4>Serenique Assistant</h4>
                <span class="status-online">● Online</span>
            </div>
            <button id="chatbot-minimize" class="chatbot-minimize">−</button>
        </div>

        <div id="chatbot-messages" class="chatbot-messages">
            <div class="chat-message bot">
                <div class="message-avatar">🤖</div>
                <div class="message-content">
                    <p>Hi there! 👋 Welcome to Serenique!</p>
                    <p>I'm here to help you with:</p>
                    <div class="quick-replies">
                        <button class="quick-reply" data-message="I need help with my order">📦 Order Help</button>
                        <button class="quick-reply" data-message="What products do you recommend?">💄 Product Recommendations</button>
                        <button class="quick-reply" data-message="Tell me about your deals">🎁 Special Offers</button>
                        <button class="quick-reply" data-message="How do I return a product?">↩️ Returns</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Type your message..." autocomplete="off">
            <button id="chatbot-send" class="chatbot-send">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
/* Chatbot Styles */
#chatbot-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: 'Poppins', sans-serif;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #d27b5a 0%, #e8a87c 100%);
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(210, 123, 90, 0.4);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.chatbot-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(210, 123, 90, 0.5);
}

.chatbot-toggle .close-icon {
    display: none;
    color: white;
    font-size: 1.5rem;
}

.chatbot-toggle.active .chat-icon { display: none; }
.chatbot-toggle.active .close-icon { display: block; }

.chatbot-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    max-width: calc(100vw - 40px);
    height: 500px;
    max-height: calc(100vh - 150px);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.chatbot-window.active { display: flex; }

.chatbot-header {
    background: linear-gradient(135deg, #d27b5a 0%, #e8a87c 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-avatar {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.chatbot-info h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.status-online {
    font-size: 0.75rem;
    opacity: 0.9;
}

.chatbot-minimize {
    margin-left: auto;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    line-height: 1;
}

.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f5f2;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.chat-message {
    display: flex;
    gap: 10px;
    max-width: 85%;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.chat-message.bot { align-self: flex-start; }
.chat-message.user { align-self: flex-end; flex-direction: row-reverse; }

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.chat-message.bot .message-avatar { background: #e8e8e8; }
.chat-message.user .message-avatar { background: #d27b5a; }

.message-content {
    background: white;
    padding: 12px 16px;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.chat-message.user .message-content {
    background: #d27b5a;
    color: white;
}

.message-content p {
    margin: 0 0 8px 0;
    line-height: 1.5;
}

.message-content p:last-child { margin-bottom: 0; }

.quick-replies {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.quick-reply {
    background: #f8f5f2;
    border: 1px solid #e0d5cc;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
}

.quick-reply:hover {
    background: #d27b5a;
    color: white;
    border-color: #d27b5a;
}

.chatbot-input-area {
    padding: 15px;
    background: white;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}

#chatbot-input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s;
}

#chatbot-input:focus { border-color: #d27b5a; }

.chatbot-send {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #d27b5a;
    border: none;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-send:hover { background: #b8654a; transform: scale(1.05); }

.typing-indicator {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #999;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-8px); }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .chatbot-window { background: #1a1a1a; }
    .chatbot-messages { background: #2d2d2d; }
    .message-content { background: #3a3a3a; color: #eee; }
    .chat-message.user .message-content { background: #d27b5a; }
    #chatbot-input { background: #2d2d2d; color: #eee; border-color: #444; }
    .chatbot-input-area { background: #1a1a1a; border-color: #333; }
    .quick-reply { background: #3a3a3a; color: #eee; border-color: #555; }
}

/* Mobile responsive */
@media (max-width: 480px) {
    .chatbot-window {
        width: calc(100vw - 20px);
        right: -10px;
        bottom: 70px;
        height: calc(100vh - 100px);
    }
}
</style>

<script>
(function() {
    const chatbotResponses = {
        greetings: [
            "Hello! How can I help you today? 😊",
            "Hi there! Welcome to Serenique! What can I assist you with?",
            "Hey! Great to see you! How may I help?"
        ],
        
        order: {
            keywords: ["order", "track", "shipping", "delivery", "where is my"],
            responses: [
                "I can help you with your order! 📦\n\nTo check your order status:\n1. Click on your profile icon\n2. Select 'My Orders'\n3. View all your past and current orders\n\nOr go directly to: <a href='/orders.php'>My Orders</a>",
                "For order tracking, please visit your <a href='/orders.php'>Order History</a>. You'll find all delivery updates there!"
            ]
        },
        
        products: {
            keywords: ["product", "recommend", "best", "popular", "what should", "suggestion"],
            responses: [
                "Great question! 💄 Here are our bestsellers:\n\n• <b>Rose Hydrating Serum</b> - Perfect for dry skin\n• <b>Vitamin C Brightening Serum</b> - For glowing skin\n• <b>Retinol Night Cream</b> - Anti-aging favorite\n\n<a href='/products.php'>Browse All Products →</a>",
                "I'd love to help you find the perfect product! What's your skin type or what are you looking for? We have amazing options in:\n\n• <a href='/products.php?category=Skincare'>Skincare</a>\n• <a href='/products.php?category=Makeup'>Makeup</a>\n• <a href='/products.php?category=Haircare'>Haircare</a>"
            ]
        },
        
        deals: {
            keywords: ["deal", "offer", "discount", "sale", "promotion", "3 for 2", "save"],
            responses: [
                "🎁 <b>AMAZING NEWS!</b> We have a 3 FOR 2 offer!\n\nBuy any 3 products and get the cheapest one <b>FREE!</b>\n\nThe discount is automatically applied at checkout. Mix and match any products!\n\n<a href='/products.php'>Start Shopping →</a>"
            ]
        },
        
        returns: {
            keywords: ["return", "refund", "exchange", "money back", "cancel"],
            responses: [
                "↩️ <b>Our Return Policy:</b>\n\n• 30-day free returns\n• Products must be unused and sealed\n• Refunds processed within 5-7 days\n\nTo start a return, please email us at support@serenique.com or use our <a href='/contact.php'>Contact Form</a>."
            ]
        },
        
        account: {
            keywords: ["account", "login", "register", "password", "sign up", "profile"],
            responses: [
                "For account help:\n\n• <a href='/login.php'>Login</a> to your account\n• <a href='/register.php'>Create new account</a>\n• Forgot password? Use the reset link on login page\n\nNeed more help? Contact our support team!"
            ]
        },
        
        contact: {
            keywords: ["contact", "support", "help", "speak", "human", "agent", "email", "phone"],
            responses: [
                "📞 <b>Contact Us:</b>\n\n• Email: support@serenique.com\n• Phone: +44 123 456 7890\n• Hours: Mon-Fri 9am-6pm\n\nOr use our <a href='/contact.php'>Contact Form</a> and we'll get back to you within 24 hours!"
            ]
        },
        
        payment: {
            keywords: ["payment", "pay", "card", "checkout", "visa", "mastercard"],
            responses: [
                "💳 <b>Payment Options:</b>\n\nWe accept:\n• Visa\n• Mastercard\n• American Express\n\nAll payments are secure and encrypted. Free shipping on all orders!"
            ]
        },
        
        thanks: {
            keywords: ["thank", "thanks", "helpful", "great", "awesome", "perfect"],
            responses: [
                "You're welcome! 😊 Is there anything else I can help you with?",
                "Happy to help! Let me know if you have any other questions!",
                "Glad I could assist! Enjoy your shopping! 🛍️"
            ]
        },
        
        bye: {
            keywords: ["bye", "goodbye", "see you", "later", "exit"],
            responses: [
                "Goodbye! Thanks for chatting with Serenique! 👋 Have a beautiful day!",
                "Take care! Come back anytime you need help! 💕"
            ]
        },
        
        default: [
            "I'm not sure I understand. Could you rephrase that? Or try one of these:\n\n• Order tracking\n• Product recommendations\n• Special offers\n• Returns & refunds",
            "Hmm, I didn't quite catch that. Would you like to:\n\n• <a href='/products.php'>Browse products</a>\n• <a href='/orders.php'>Check orders</a>\n• <a href='/contact.php'>Contact support</a>"
        ]
    };

    function getResponse(message) {
        const msg = message.toLowerCase();
        
        // Check each category
        for (const [category, data] of Object.entries(chatbotResponses)) {
            if (category === 'greetings' || category === 'default') continue;
            
            if (data.keywords && data.keywords.some(keyword => msg.includes(keyword))) {
                return data.responses[Math.floor(Math.random() * data.responses.length)];
            }
        }
        
        // Check for greetings
        if (/^(hi|hello|hey|good morning|good afternoon|good evening)/i.test(msg)) {
            return chatbotResponses.greetings[Math.floor(Math.random() * chatbotResponses.greetings.length)];
        }
        
        // Default response
        return chatbotResponses.default[Math.floor(Math.random() * chatbotResponses.default.length)];
    }

    function addMessage(content, isUser = false) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user' : 'bot'}`;
        
        messageDiv.innerHTML = `
            <div class="message-avatar">${isUser ? '👤' : '🤖'}</div>
            <div class="message-content">
                <p>${content}</p>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function showTyping() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chat-message bot';
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-avatar">🤖</div>
            <div class="message-content">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function hideTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    function sendMessage(message) {
        if (!message.trim()) return;
        
        // Add user message
        addMessage(message, true);
        
        // Clear input
        document.getElementById('chatbot-input').value = '';
        
        // Show typing indicator
        showTyping();
        
        // Simulate response delay
        setTimeout(() => {
            hideTyping();
            const response = getResponse(message);
            addMessage(response);
        }, 1000 + Math.random() * 500);
    }

    // Initialize chatbot
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('chatbot-toggle');
        const window = document.getElementById('chatbot-window');
        const minimize = document.getElementById('chatbot-minimize');
        const input = document.getElementById('chatbot-input');
        const sendBtn = document.getElementById('chatbot-send');

        // Toggle chat window
        toggle.addEventListener('click', () => {
            toggle.classList.toggle('active');
            window.classList.toggle('active');
            if (window.classList.contains('active')) {
                input.focus();
            }
        });

        // Minimize
        minimize.addEventListener('click', () => {
            toggle.classList.remove('active');
            window.classList.remove('active');
        });

        // Send message
        sendBtn.addEventListener('click', () => sendMessage(input.value));
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage(input.value);
        });

        // Quick replies
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-reply')) {
                sendMessage(e.target.dataset.message);
            }
        });
    });
})();
</script>

