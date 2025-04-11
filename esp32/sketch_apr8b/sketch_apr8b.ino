#include <WiFi.h>

const char *ssid = "SLT FIBER";
const char *password = "aw2233072";

WiFiServer server(80);  // Use WiFiServer instead of NetworkServer

void setup() {
  Serial.begin(115200);

  // Set LED pins as outputs
  pinMode(2, OUTPUT);
  pinMode(4, OUTPUT);
  pinMode(5, OUTPUT);

  delay(10);

  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("WiFi connected.");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  server.begin();
}

void loop() {
  WiFiClient client = server.available();  // Listen for incoming clients

  if (client) {
    Serial.println("New Client.");
    String currentLine = "";

    while (client.connected()) {
      if (client.available()) {
        char c = client.read();
        Serial.write(c);
        if (c == '\n') {
          if (currentLine.length() == 0) {
            // Send HTTP response
            client.println("HTTP/1.1 200 OK");
            client.println("Content-type:text/html");
            client.println();

            client.println("<h1>ESP32 LED Control</h1>");
            client.println("<p><a href=\"/H2\">Turn ON LED on pin 2</a></p>");
            client.println("<p><a href=\"/L2\">Turn OFF LED on pin 2</a></p>");
            client.println("<p><a href=\"/H4\">Turn ON LED on pin 4</a></p>");
            client.println("<p><a href=\"/L4\">Turn OFF LED on pin 4</a></p>");
            client.println("<p><a href=\"/H5\">Turn ON LED on pin 5</a></p>");
            client.println("<p><a href=\"/L5\">Turn OFF LED on pin 5</a></p>");
            client.println();

            break;
          } else {
            currentLine = "";
          }
        } else if (c != '\r') {
          currentLine += c;
        }

        // Control each LED based on URL
        if (currentLine.endsWith("GET /H2")) {
          digitalWrite(2, HIGH);
        } else if (currentLine.endsWith("GET /L2")) {
          digitalWrite(2, LOW);
        } else if (currentLine.endsWith("GET /H4")) {
          digitalWrite(4, HIGH);
        } else if (currentLine.endsWith("GET /L4")) {
          digitalWrite(4, LOW);
        } else if (currentLine.endsWith("GET /H5")) {
          digitalWrite(5, HIGH);
        } else if (currentLine.endsWith("GET /L5")) {
          digitalWrite(5, LOW);
        }
      }
    }

    client.stop();
    Serial.println("Client Disconnected.");
  }
}
