default : dist

dist : test
	git archive HEAD:src/MasterCard -o ./module-mastercard.zip

install :
	@composer.phar install --no-progress --no-suggest

test : install
	@composer.phar phpcs
	@composer.phar phpstan