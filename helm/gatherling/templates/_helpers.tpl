{{/*
Base name
*/}}
{{- define "gatherling.name" -}}
{{- .Chart.Name | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Resource names
*/}}
{{- define "gatherling.fullname" -}}
{{- if contains .Chart.Name .Release.Name }}
{{- .Release.Name | trunc 40 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name .Chart.Name | trunc 40 | trimSuffix "-" }}
{{- end }}
{{- end }}

{{- define "gatherling.fullname.mysql" -}}
{{- printf "%s-mysql" (include "gatherling.fullname" .) -}}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "gatherling.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "gatherling.labels" -}}
helm.sh/chart: {{ include "gatherling.chart" . }}
{{ include "gatherling.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "gatherling.selectorLabels" -}}
app.kubernetes.io/name: {{ include "gatherling.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}
