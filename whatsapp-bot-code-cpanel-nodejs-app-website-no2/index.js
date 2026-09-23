import { makeWASocket, useMultiFileAuthState, DisconnectReason } from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import pino from 'pino';
import qrcode from 'qrcode-terminal';
import fetch from 'node-fetch';

// ==========================================
// CONFIGURATION
// ==========================================
const WEBSITE_URL = 'https://yourwebsite.com'; // <-- REPLACE with your actual website domain/folder
const POLLING_INTERVAL_MS = 5000; // Checks for new messages every 5 seconds

let sock;

async function connectToWhatsApp() {
    // This saves your login session inside the 'auth_info_baileys' folder
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');

    sock = makeWASocket({
        auth: state,
        printQRInTerminal: true,
        logger: pino({ level: 'silent' }) // Keeps terminal logs clean
    });

    // Handle connection updates (QR code, reconnection, etc.)
    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log('📱 Scan this QR code with your WhatsApp app (Only needed once):');
            qrcode.generate(qr, { small: true });
        }

        if (connection === 'close') {
            const reason = new Boom(lastDisconnect?.error)?.output?.statusCode;
            console.log('⚠️ Connection closed, reconnecting...');
            
            // Reconnect unless logged out explicitly
            if (reason !== DisconnectReason.loggedOut) {
                connectToWhatsApp();
            } else {
                console.log('❌ Logged out. Please delete the "auth_info_baileys" folder and rescan the QR code.');
            }
        } else if (connection === 'open') {
            console.log('✅ WhatsApp bot is connected successfully!');
            startMessagePolling(); // Start checking your website database for pending messages
        }
    });

    // Save credentials whenever they update (tokens, keys, etc.)
    sock.ev.on('creds.update', saveCreds);
}

// ==========================================
// POLLING WORKER: Check Website & Send Messages
// ==========================================
function startMessagePolling() {
    console.log(`🔄 Polling website for pending messages every ${POLLING_INTERVAL_MS / 1000} seconds...`);

    setInterval(async () => {
        if (!sock) return;

        try {
            // 1. Fetch pending messages from your website
            const response = await fetch(`${WEBSITE_URL}/get-pending.php`);
            const data = await response.json();

            if (!data.success || !data.messages || data.messages.length === 0) {
                return; // No pending messages, do nothing
            }

            console.log(`📥 Found ${data.messages.length} pending message(s) from website.`);

            // 2. Loop through each pending message and send it
            for (const item of data.messages) {
                // Format phone number properly (ensure it includes WhatsApp suffix)
                const formattedPhone = item.phone.includes('@s.whatsapp.net') 
                    ? item.phone 
                    : `${item.phone}@s.whatsapp.net`;

                console.log(`🚀 Sending message ID ${item.id} to ${item.phone}...`);

                try {
                    // Send message via Baileys WhatsApp socket
                    await sock.sendMessage(formattedPhone, { text: item.message });

                    // 3. Update status on website to 'sent'
                    await updateMessageStatus(item.id, 'sent');
                    console.log(`✅ Message ID ${item.id} sent and status updated to 'sent'!`);

                } catch (sendError) {
                    console.error(`❌ Failed to send message ID ${item.id}:`, sendError.message);
                    // Optional: mark as failed if needed
                    await updateMessageStatus(item.id, 'failed');
                }
            }

        } catch (error) {
            console.error('⚠️ Error during polling:', error.message);
        }
    }, POLLING_INTERVAL_MS);
}

// Helper function to update message status via your PHP endpoint
async function updateMessageStatus(id, status) {
    try {
        await fetch(`${WEBSITE_URL}/update-status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
        });
    } catch (error) {
        console.error(`⚠️ Failed to sync status update for ID ${id}:`, error.message);
    }
}

// Start the bot
connectToWhatsApp();