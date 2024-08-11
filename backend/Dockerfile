FROM yiisoftware/yii2-php:7.4-apache

# Install Python and pip
RUN apt-get update && apt-get install -y python3 python3-pip

# Install Python packages
COPY requirements.txt /app/requirements.txt
RUN pip3 install -r /app/requirements.txt

# Copy the application code
COPY . /app

WORKDIR /app

# Expose the port the app runs on
EXPOSE 80

# Start the server
CMD ["apache2-foreground"]
