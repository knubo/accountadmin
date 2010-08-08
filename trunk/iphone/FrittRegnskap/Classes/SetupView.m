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

- (BOOL)textFieldShouldReturn:(UITextField *)textField {
	[textField resignFirstResponder];
	return NO;
}


@end
