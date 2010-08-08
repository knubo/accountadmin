//
//  SetupView.m
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "SetupView.h"

@implementation SetupView


@synthesize domain;
@synthesize username;
@synthesize password;


- (void)dealloc {
	
    [domain release];
    [username release];
    [password release];
	
    [super dealloc];
}

- (void) loadSettings {
	
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  

	username.text = [userDefaults stringForKey:@"frittregnskap_username"];
	domain.text = [userDefaults stringForKey:@"frittregnskap_domain"];
	password.text = [userDefaults stringForKey:@"frittregnskap_password"];

}

- (void) saveSettings {
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
	
	[userDefaults setObject:username.text forKey:@"frittregnskap_username"];
	[userDefaults setObject:domain.text forKey:@"frittregnskap_domain"];
	[userDefaults setObject:password.text forKey:@"frittregnskap_password"];
	[userDefaults synchronize];
		
	[userDefaults release];
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField {
	[textField resignFirstResponder];
	return NO;
}


@end
