//
//  PersonDetailsUIController.h
//
//  Created by Knut Erik Borgen on 15.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#include "model/Person.h"

@interface PersonDetailsUIController : UIViewController {

	IBOutlet UITextView *commentsView;
	IBOutlet UILabel *firstname;
	IBOutlet UILabel *lastname;
	IBOutlet UILabel *address;
	IBOutlet UILabel *email;
	IBOutlet UILabel *postnmb;
	IBOutlet UILabel *city;
	IBOutlet UILabel *country;
	IBOutlet UILabel *sex;
	IBOutlet UILabel *newsletter;
	IBOutlet UILabel *employee;
	IBOutlet UILabel *phone;
	IBOutlet UILabel *cellphone;
	IBOutlet UITextView *comment;
}

- (void)showPersonDetail:(Person*) person;

@end
