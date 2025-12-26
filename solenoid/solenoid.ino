/*
 * Sentinel Door Lock IoT - Server-Driven Actuator with Feedback
 * 
 * Hardware: NodeMCU ESP8266 + Relay 1CH 5V + Solenoid Door Lock + LEDs + Buzzer
 * 
 * This device is a pure actuator controlled by the server.
 * It does NOT make access decisions - only executes commands.
 * 
 * Pin Configuration:
 *   D0 - Relay (Solenoid)
 *   D1 - Green LED (Access Granted)
 *   D3 - Red LED (Access Denied)
 *   D8 - Buzzer
 */

#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include "config.h"

// Pin definitions
#define RELAY_PIN   D0
#define LED_HIJAU   D1
#define LED_MERAH   D3
#define BUZZER      D8

// Door states
enum DoorState { LOCKED, UNLOCKED };
DoorState currentState = LOCKED;

ESP8266WebServer server(80);

// ============================================================
// Helper Functions
// ============================================================

void setDoorState(DoorState state) {
    currentState = state;
    if (state == UNLOCKED) {
        digitalWrite(RELAY_PIN, HIGH);
        Serial.println("[DOOR] UNLOCKED");
    } else {
        digitalWrite(RELAY_PIN, LOW);
        Serial.println("[DOOR] LOCKED");
    }
}

bool validateSecret() {
    if (!server.hasHeader("X-API-Secret")) {
        return false;
    }
    return server.header("X-API-Secret") == API_SECRET;
}

void sendJsonResponse(int code, String message) {
    String json = "{\"status\":\"" + message + "\",\"door\":\"" + 
                  (currentState == LOCKED ? "locked" : "unlocked") + "\"}";
    server.send(code, "application/json", json);
}

// Feedback functions with LED and buzzer
void feedbackSuccess() {
    digitalWrite(LED_HIJAU, HIGH);
    
    // Double beep for success
    for (int i = 0; i < 2; i++) {
        digitalWrite(BUZZER, HIGH);
        delay(100);
        digitalWrite(BUZZER, LOW);
        delay(100);
    }
    
    delay(500);
    digitalWrite(LED_HIJAU, LOW);
}

void feedbackDenied() {
    digitalWrite(LED_MERAH, HIGH);
    digitalWrite(BUZZER, HIGH);
    delay(1500);
    digitalWrite(BUZZER, LOW);
    digitalWrite(LED_MERAH, LOW);
}

// ============================================================
// HTTP Handlers
// ============================================================

void handleUnlock() {
    if (!validateSecret()) {
        feedbackDenied();
        sendJsonResponse(401, "unauthorized");
        return;
    }
    
    setDoorState(UNLOCKED);
    feedbackSuccess();
    sendJsonResponse(200, "unlocked");
}

void handleLock() {
    if (!validateSecret()) {
        feedbackDenied();
        sendJsonResponse(401, "unauthorized");
        return;
    }
    
    setDoorState(LOCKED);
    sendJsonResponse(200, "locked");
}

void handleStatus() {
    String json = "{\"door\":\"" + String(currentState == LOCKED ? "locked" : "unlocked") + 
                  "\",\"uptime\":" + String(millis() / 1000) + 
                  ",\"ip\":\"" + WiFi.localIP().toString() + "\"}";
    server.send(200, "application/json", json);
}

void handleNotFound() {
    server.send(404, "application/json", "{\"error\":\"not found\"}");
}

// ============================================================
// Setup & Loop
// ============================================================

void setup() {
    Serial.begin(115200);
    Serial.println("\n[SENTINEL] Door Lock IoT Starting...");

    // Initialize pins
    pinMode(RELAY_PIN, OUTPUT);
    pinMode(LED_HIJAU, OUTPUT);
    pinMode(LED_MERAH, OUTPUT);
    pinMode(BUZZER, OUTPUT);

    // Set default states
    digitalWrite(RELAY_PIN, LOW);
    digitalWrite(LED_HIJAU, LOW);
    digitalWrite(LED_MERAH, LOW);
    digitalWrite(BUZZER, LOW);
    
    setDoorState(LOCKED);

    // Connect to WiFi
    Serial.print("[WIFI] Connecting to ");
    Serial.println(WIFI_SSID);
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 30) {
        delay(500);
        Serial.print(".");
        attempts++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println();
        Serial.print("[WIFI] Connected! IP: ");
        Serial.println(WiFi.localIP());
        
        // Success blink on WiFi connect
        feedbackSuccess();
    } else {
        Serial.println("\n[WIFI] Failed to connect. Restarting...");
        feedbackDenied();
        ESP.restart();
    }

    // Collect headers for API secret validation
    server.collectHeaders("X-API-Secret");

    // Setup HTTP routes
    server.on("/unlock", HTTP_POST, handleUnlock);
    server.on("/lock", HTTP_POST, handleLock);
    server.on("/status", HTTP_GET, handleStatus);
    server.onNotFound(handleNotFound);

    // Start server
    server.begin();
    Serial.println("[HTTP] Server started on port 80");
    Serial.println("[READY] Waiting for commands...");
}

void loop() {
    server.handleClient();
}
