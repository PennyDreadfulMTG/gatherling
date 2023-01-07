# Gatherling Helm Chart

This Helm chart runs the Gatherling application, and supports running it in development and production environments.

## Development

If you're using Rancher Desktop, you'll need to go into Preferences and tell it to port forward the Gatherling service so that it maps to port 30080. This matches up with the default `http://127.0.0.1:30080/` URL set in `values.yaml`:

![You need to port forward in the Rancher Desktop preferences for local development to work.](./rancher-desktop-port-forward.png)

If you change the URL to be something else in `values.yaml`, you should map the port differently in Rancher.

## Production

For a production deploy, you'll want to use Skaffold to build the Docker image and install or upgrade the application to your Kubernetes cluster.

### Setting up secrets

Production deployments rely on secrets already existing in your Kubernetes cluster, with the Helm values naming the secrets you want to use. For example, if you wanted to create the secret for the MySQL password, you would run something like this on your local command line:

```
kubectl -n default create secret generic mysql-password --from-literal=password=...
```

And then when configuring the values inside the generated `production-values.yaml` file (see below), you'd provide something like this:

```yaml
mysql:
  host: ...
  user: ...
  database: ...
  passwordSecret:
    name: mysql-password
    key: password
```

You would do this for all the secrets you need.

### Running a deployment

You'll need to make a copy of `skaffold.yaml` for your own deployment environment, and set the values appropriately. The example script below shows how to dynamically generate your own `production-skaffold.yaml` and `production-values.yaml` override files and use them in the build process:

```bash
# General deployment settings
CONTAINER_REGISTRY=ghcr.io/pennydreadfulmtg/gatherling
KUBERNETES_NAMESPACE=default

# Generate our skaffold.yaml and values.yaml files.
cat >production-skaffold.yaml <<EOF
apiVersion: skaffold/v4beta1
kind: Config
build:
  artifacts:
  - image: $CONTAINER_REGISTRY/app
    context: .
    docker:
      dockerfile: Dockerfile
  local:
    push: true
    useBuildkit: true
    concurrency: 0
manifests:
  helm:
    flags:
      install:
      - --debug
      - --atomic
      upgrade:
      - --debug
      - --atomic
    releases:
    - name: gatherling
      chartPath: helm/gatherling
      namespace: $KUBERNETES_NAMESPACE
      setFiles:
        - production-values.yaml
      setValues:
        app.image: $CONTAINER_REGISTRY/app
deploy:
  helm: {}
EOF
cat >production-values.yaml <<EOF
environment:
  type: production

app:
  name: "Gatherling.com"
  host: "gatherling.com"
  url: "https://gatherling.com/"
  replicaCount: 2

# Refer to the values.yaml file in this folder for other settings you could set here.
EOF

# Ensure our generated files are cleaned up regardless of the build result.
mkdir "$(pwd)/.docker" || true
export DOCKER_CONFIG="$(pwd)/.docker"
trap 'rm production-skaffold.yaml production-values.yaml && rm -rf .docker' EXIT

# Authorize to registry
# For GitHub Actions:
#   Set GITHUB_ACTOR=${{ github.actor }} and GITHUB_TOKEN=${{ secrets.GITHUB_TOKEN }}
#
# echo "$GITHUB_TOKEN" | docker login ghcr.io -u $GITHUB_ACTOR --password-stdin
#
# For GitLab:
#
# docker login -u $CI_REGISTRY_USER -p $CI_REGISTRY_PASSWORD $CI_REGISTRY

# If you are using Sentry and want to create releases on Sentry in line
# with deployments, keep the Sentry commands below and set the following
# environment variables:
# SENTRY_PROJECT=myproject
# SENTRY_AUTH_TOKEN=...
# SENTRY_ORG=...
# SENTRY_URL=... (if not sentry.io)

# Set up version in Sentry
if [ "$SENTRY_PROJECT" != "" ]; then
  SENTRY_RELEASE="$(sentry-cli releases -p "$SENTRY_PROJECT" propose-version)"
  sentry-cli releases -p "$SENTRY_PROJECT" new "$SENTRY_RELEASE"
  sentry-cli releases -p "$SENTRY_PROJECT" set-commits --auto "$SENTRY_RELEASE" --ignore-missing --ignore-empty
else
  SENTRY_RELEASE="unversioned"
fi

# Build the application
skaffold build -f production-values.yaml -v debug --file-output "build-$SENTRY_RELEASE.json" --cache-artifacts=false

# Finalize the version in Sentry once we know it built successfully
if [ "$SENTRY_PROJECT" != "" ]; then
  sentry-cli releases -p "$SENTRY_PROJECT" finalize "$SENTRY_RELEASE"
fi

# Deploy the application
start=$(date +%s)
skaffold deploy -f production-values.yaml -a build-$SENTRY_RELEASE.json -v debug --label="sentryRelease=$SENTRY_RELEASE"
now=$(date +%s)

# Mark the application as deployed in Sentry. Replace https://gatherling.com with
# the actual deployment URL of the application.
if [ "$SENTRY_PROJECT" != "" ]; then
  sentry-cli releases -p "$SENTRY_PROJECT" deploys "$SENTRY_RELEASE" new -e Production -t $((now-start)) -n "$CI_PIPELINE_ID" -u "https://gatherling.com"
fi
```