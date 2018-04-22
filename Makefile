all:
	if [[ -e payment_begateway.zip ]]; then rm payment_begateway.zip; fi
	cd payment_begateway && zip -r ../payment_begateway.zip . -x "*/test/*" -x "*/.git/*" -x "*/examples/*"
