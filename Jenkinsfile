pipeline {
  agent any
  stages {
    stage('Hello world docker') {
      steps {
        parallel(
          "Hello world docker": {
            sh 'docker run hello-world'
            
          },
          "": {
            sh '''current_workspace=$(echo $PWD | sed "s|/var/jenkins_home/workspace|$HOST_WORKSPACE|g")
docker run -v $current_workspace:/app --rm -i composer/composer install
docker run -v $current_workspace:/app --rm -i phpunit/phpunit:5.0.3 -c phpunit.xml'''
            
          }
        )
      }
    }
  }
}