# Weather App by Albert Jonathan

## Installation (available in script called install.sh)
./vendor/bin/sail composer install
./vendor/bin/sail bun install
./vendor/bin/sail cp .env.example .env 
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail psql \i ./database/functions.sql
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan queue:listen
./vendor/bin/bun run dev

Database schema is in Database ERD.svg
API Documentation wasn't being made due to time constraints


# What I have done so far

## Key Features to Implement
- [X] Implement RESTful APIs using Laravel
- [X] Integrate with a free weather data providers (e.g., OpenWeatherMap, WeatherAPI.com)
- [X] Create a weather alert system with configurable rules
- [X] Handle background processing for data collection and analysis
- [X] Implement proper error handling and API rate limiting
- [X] Use PostgreSQL for data storage

### Weather Data Collection and Aggregation
- [X] Weather data collection and aggregation
- [X] Historical data analysis
- [X] Alert rule engine
- [X] Scheduled data fetching
- [X] API rate limiting and caching

## Database Design (PostgreSQL)
- [X] Design schema for weather data storage
- [X] Implement time-series data handling
- [ ] Create materialized views for performance optimization
- [ ] Design proper indexing strategy
- [ ] Implement data partitioning for historical data
- [X] Handle geospatial queries
- [X] Create database functions for common calculations

## Frontend
Build a dashboard that includes:
- [X] Weather data visualization using charts and graphs
- [X] Real-time updates for current conditions
- [X] Alert management interface
- [X] Historical data analysis views
- [x] Interactive weather maps
- [x] Responsive design for mobile and desktop

## Infrastructure
- [X] Set up Docker environment with docker-compose
- [X] Configure services for:
  - [X] Laravel backend
  - [X] PostgreSQL database
  - [X] Any additional required services if needed
- [X] Implement proper service networking

## Testing Requirements

### Laravel Tests
- [ ] Unit tests for business logic
- [ ] Feature tests for API endpoints
- [ ] Integration tests for external API communication

### Frontend Tests
- [ ] Component testing
- [ ] Integration testing
- [ ] User interaction testing
- [ ] API integration testing

## Documentation:
- [X] Comprehensive README with setup instructions
- [ ] API documentation (OpenAPI/Swagger)
- [X] Database schema documentation
- [ ] Testing documentation
## ocker Configuration:
- [X] docker-compose.yml
- [X] Service Dockerfiles
- [X] Environment configuration files