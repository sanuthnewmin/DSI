# Use Grafana OSS version 11.5.1 as the base image
FROM grafana/grafana-oss:11.5.1

##################################################################
# CONFIGURATION
##################################################################

# Set Grafana environment variables
ENV GF_ENABLE_GZIP=true \
    GF_USERS_DEFAULT_THEME=light \
    GF_AUTH_ANONYMOUS_ENABLED=true \
    GF_AUTH_BASIC_ENABLED=false \
    GF_PANELS_DISABLE_SANITIZE_HTML=true \
    GF_ANALYTICS_CHECK_FOR_UPDATES=false \
    GF_DASHBOARDS_DEFAULT_HOME_DASHBOARD_PATH=/etc/grafana/provisioning/dashboards/business.json \
    GF_PATHS_PROVISIONING="/etc/grafana/provisioning" \
    GF_PATHS_PLUGINS="/var/lib/grafana/plugins"

##################################################################
# COPY ARTIFACTS
##################################################################

# Copy application files
COPY --chown=grafana:root dist /app
COPY entrypoint.sh /

# Copy provisioning files
COPY --chown=grafana:root provisioning $GF_PATHS_PROVISIONING

##################################################################
# CUSTOMIZATION (Branding & UI)
##################################################################

USER root

# Replace favicon, logo, and background images
COPY img/fav32.png /usr/share/grafana/public/img
COPY img/fav32.png /usr/share/grafana/public/img/apple-touch-icon.png
COPY img/logo.svg /usr/share/grafana/public/img/grafana_icon.svg
COPY img/background.svg /usr/share/grafana/public/img/g8_login_dark.svg
COPY img/background.svg /usr/share/grafana/public/img/g8_login_light.svg

# Update title and loading message
RUN sed -i 's|<title>\[\[.AppTitle\]\]</title>|<title>SenseGrid</title>|g' /usr/share/grafana/public/views/index.html && \
    sed -i 's|Loading Grafana|Loading Business Suite|g' /usr/share/grafana/public/views/index.html

# Modify navigation menu
RUN sed -i "s|\[\[.NavTree\]\],|nav,|g; \
    s|window.grafanaBootData = {| \
    let nav = [[.NavTree]]; \
    const dashboards = nav.find((element) => element.id === 'dashboards/browse'); \
    if (dashboards) { dashboards['children'] = [];} \
    const connections = nav.find((element) => element.id === 'connections'); \
    if (connections) { connections['url'] = '/datasources'; connections['children'].shift(); } \
    const help = nav.find((element) => element.id === 'help'); \
    if (help) { help['subTitle'] = 'Business Suite 11.5.1'; help['children'] = [];} \
    window.grafanaBootData = {|g" \
    /usr/share/grafana/public/views/index.html

# Move Business App to navigation root
RUN sed -i 's|\[navigation.app_sections\]|\[navigation.app_sections\]\nbusiness-app=root|g' /usr/share/grafana/conf/defaults.ini

##################################################################
# REMOVE UNNECESSARY DATA SOURCES
##################################################################

RUN rm -rf /usr/share/grafana/public/app/plugins/datasource/elasticsearch \
    /usr/share/grafana/public/app/plugins/datasource/graphite \
    /usr/share/grafana/public/app/plugins/datasource/opentsdb \
    /usr/share/grafana/public/app/plugins/datasource/tempo \
    /usr/share/grafana/public/app/plugins/datasource/jaeger \
    /usr/share/grafana/public/app/plugins/datasource/zipkin \
    /usr/share/grafana/public/app/plugins/datasource/azuremonitor \
    /usr/share/grafana/public/app/plugins/datasource/cloudwatch \
    /usr/share/grafana/public/app/plugins/datasource/cloud-monitoring \
    /usr/share/grafana/public/app/plugins/datasource/parca \
    /usr/share/grafana/public/app/plugins/datasource/phlare \
    /usr/share/grafana/public/app/plugins/datasource/grafana-pyroscope-datasource

##################################################################
# REMOVE UNNECESSARY PANELS
##################################################################

RUN rm -rf /usr/share/grafana/public/app/plugins/panel/alertlist \
    /usr/share/grafana/public/app/plugins/panel/annolist \
    /usr/share/grafana/public/app/plugins/panel/dashlist \
    /usr/share/grafana/public/app/plugins/panel/news \
    /usr/share/grafana/public/app/plugins/panel/geomap \
    /usr/share/grafana/public/app/plugins/panel/table-old \
    /usr/share/grafana/public/app/plugins/panel/traces \
    /usr/share/grafana/public/app/plugins/panel/flamegraph

##################################################################
# REMOVE UNNECESSARY PLUGINS
##################################################################

RUN rm -rf /var/lib/grafana/plugins/grafana-piechart-panel \
    /var/lib/grafana/plugins/grafana-polystat-panel \
    /var/lib/grafana/plugins/grafana-worldmap-panel \
    /var/lib/grafana/plugins/grafana-clock-panel \
    /var/lib/grafana/plugins/grafana-gantt-panel \
    /var/lib/grafana/plugins/grafana-image-renderer \
    /var/lib/grafana/plugins/grafana-simple-json-datasource \
    /var/lib/grafana/plugins/grafana-csv-datasource

##################################################################
# MODIFY JAVASCRIPT FILES (Remove Unwanted Features)
##################################################################

RUN find /usr/share/grafana/public/build/ -name "*.js" \
  -exec sed -i 's|AppTitle="SenseGrid"|AppTitle="SenseGrid"|g' {} \; \
  -exec sed -i 's|LoginTitle="Welcome to SenseGrid"|LoginTitle="Welcome to SenseGrid"|g' {} \; \
  -exec sed -i 's|\[{target:"_blank",id:"documentation".*grafana_footer"}\]|\[\]|g' {} \; \
  -exec sed -i 's|({target:"_blank",id:"license",.*licenseUrl})|()|g' {} \; \
  -exec sed -i 's|({target:"_blank",id:"version",text:..versionString,url:D?"https://github.com/grafana/grafana/blob/main/CHANGELOG.md":void 0})|()|g' {} \; \
  -exec sed -i 's|(0,t.jsx)(...,{className:ge,onClick:.*,iconOnly:!0,icon:"rss","aria-label":"News"})|null|g' {} \; \
  -exec sed -i 's|(0,t.jsx)(u.I,{tooltip:"Switch to old dashboard page",icon:"apps",onClick:()=>{s.Ny.partial({scenes:!1})}},"view-in-old-dashboard-button")|null|g' {} \;


  FROM php:8.2-apache

  # Install dependencies and enable mysqli extension
  RUN docker-php-ext-install mysqli && \
      a2enmod rewrite
  
  # Copy app files
  COPY . /var/www/html/
  
  # Set proper permissions
  RUN chown -R www-data:www-data /var/www/html
  






