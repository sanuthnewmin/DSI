# Project Setup Instructions

## Prerequisites
Ensure you have the following installed on your system:
- [Node.js](https://nodejs.org/) (includes npm)
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Installation Steps

1. **Clone the Repository** (If not already cloned):
   ```sh
   git clone https://github.com/RUM35H/DSI_Server.git
   cd DSI_Server
   ```

2. **Install Node.js Dependencies**:
   ```sh
   npm install
   ```

3. **Run Docker Compose**:
   ```sh
   docker-compose up -d --build
   ```

   - This will automatically set up and configure:
     - MQTT Data Source
     - MySQL Database
     - InfluxDB

4. **Verify Services**:
   - Check running containers:
     ```sh
     docker ps
     ```
   - Logs (optional, for debugging):
     ```sh
     docker-compose logs -f
     ```

## Additional Notes
- Ensure **Docker Daemon** is running before executing the commands.
- If you encounter permission issues, try running commands with `sudo`.
- Stop services when needed:
  ```sh
  docker-compose down
  ```


