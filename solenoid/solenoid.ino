/*
 * Sentinel Door Lock IoT - Server-Driven Actuator
 * 
 * Hardware: NodeMCU ESP8266 + Relay 1CH 5V + Solenoid Door Lock
 * 
 * This device is a pure actuator controlled by the server.
 * It does NOT make access decisions - only executes commands.
 */

#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include "config.h"

// Relay pin (D1 = GPIO5)
#define RELAY_PIN D1

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
        digitalWrite(RELAY_PIN, HIGH);  // Energize relay
        Serial.println("[DOOR] UNLOCKED");
    } else {
        digitalWrite(RELAY_PIN, LOW);   // De-energize relay
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

// ============================================================
// HTTP Handlers
// ============================================================

void handleUnlock() {
    if (!validateSecret()) {
        sendJsonResponse(401, "unauthorized");
        return;
    }
    setDoorState(UNLOCKED);
    sendJsonResponse(200, "unlocked");
}

void handleLock() {
    if (!validateSecret()) {
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

    // Initialize relay pin
    pinMode(RELAY_PIN, OUTPUT);
    setDoorState(LOCKED);  // Default: locked

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
    } else {
        Serial.println("\n[WIFI] Failed to connect. Restarting...");
        ESP.restart();
    }

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
